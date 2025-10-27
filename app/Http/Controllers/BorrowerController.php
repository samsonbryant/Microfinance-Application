<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use App\Models\Client;
use App\Services\LoanService;
use Illuminate\Support\Facades\Auth;

class BorrowerController extends Controller
{
    protected $loanService;

    public function __construct(LoanService $loanService)
    {
        $this->middleware('auth');
        $this->middleware('role:borrower');
        $this->loanService = $loanService;
    }

    /**
     * Ensure borrower has a client record
     */
    private function ensureClientExists($user)
    {
        if (!$user->client) {
            // Auto-create client record for borrower
            $client = Client::create([
                'client_number' => Client::generateClientNumber(),
                'user_id' => $user->id,
                'first_name' => $user->name,
                'last_name' => '',
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'address' => '',
                'date_of_birth' => null,
                'gender' => 'other',
                'marital_status' => 'single',
                'identification_type' => 'national_id',
                'identification_number' => '',
                'status' => 'active',
                'kyc_status' => 'pending',
                'branch_id' => $user->branch_id ?? 1,
                'created_by' => $user->id,
            ]);
            
            // Relationship is established via user_id on clients table
            // No need to update users table - just refresh the relationship
            $user->load('client');
            
            return $client;
        }
        
        return $user->client;
    }

    /**
     * Display the borrower dashboard with real-time Livewire component
     */
    public function dashboard()
    {
        $user = Auth::user();
        $client = $this->ensureClientExists($user);

        // Use new Livewire-powered dashboard with real-time updates
        return view('borrower.dashboard-livewire');
    }
    
    /**
     * Get count of upcoming payments in next 30 days
     */
    private function getUpcomingPaymentsCount($client)
    {
        $upcomingCount = 0;
        $loans = $client->loans()->whereIn('status', ['active', 'disbursed'])->get();
        
        foreach ($loans as $loan) {
            if ($loan->next_due_date && $loan->next_due_date->lte(now()->addDays(30))) {
                $upcomingCount++;
            }
        }
        
        return $upcomingCount;
    }

    /**
     * Display borrower's loans.
     */
    public function loans()
    {
        $user = Auth::user();
        $client = $this->ensureClientExists($user);

        $loans = $client->loans()
            ->with(['collateral', 'transactions'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('borrower.loans.index', compact('loans'));
    }

    /**
     * Display a specific loan.
     */
    public function showLoan(Loan $loan)
    {
        $this->authorize('view', $loan);
        
        $loan->load(['collateral', 'transactions']);
        
        return view('borrower.loans.show', compact('loan'));
    }

    /**
     * Display borrower's savings accounts.
     */
    public function savings()
    {
        $user = Auth::user();
        $client = $this->ensureClientExists($user);

        $savingsAccounts = $client->savingsAccounts()
            ->with(['transactions'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('borrower.savings.index', compact('savingsAccounts'));
    }

    /**
     * Display a specific savings account.
     */
    public function showSavings(SavingsAccount $savingsAccount)
    {
        $this->authorize('view', $savingsAccount);
        
        $savingsAccount->load(['transactions']);
        
        return view('borrower.savings.show', compact('savingsAccount'));
    }

    /**
     * Display borrower's transactions.
     */
    public function transactions()
    {
        $user = Auth::user();
        $client = $this->ensureClientExists($user);

        $transactions = $client->transactions()
            ->with(['loan', 'savingsAccount'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('borrower.transactions.index', compact('transactions'));
    }

    /**
     * Display borrower's profile.
     */
    public function profile()
    {
        $user = Auth::user();
        $client = $this->ensureClientExists($user);

        return view('borrower.profile', compact('user', 'client'));
    }

    /**
     * Update borrower's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'national_id' => 'nullable|string|max:50',
        ]);

        $user->update($request->only(['name', 'email', 'phone']));

        $client = $this->ensureClientExists($user);
        $client->update([
            'first_name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'national_id' => $request->national_id,
        ]);

        return redirect()->route('borrower.dashboard')->with('success', 'Profile updated successfully.');
    }

    /**
     * Display payment form.
     */
    public function paymentForm(Request $request)
    {
        $loanId = $request->get('loan_id');
        $loan = null;
        
        if ($loanId) {
            $loan = Loan::findOrFail($loanId);
            $this->authorize('view', $loan);
        }

        $user = Auth::user();
        $client = $this->ensureClientExists($user);

        $loans = $client->loans()->whereIn('status', ['active', 'disbursed'])->get();

        return view('borrower.payments.create', compact('loan', 'loans'));
    }

    /**
     * Process payment.
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money',
            'reference' => 'nullable|string|max:100',
        ]);

        $loan = Loan::findOrFail($request->loan_id);
        $this->authorize('view', $loan);

        try {
            $repayment = $this->loanService->processRepayment(
                $loan,
                $request->amount,
                $request->payment_method,
                $request->reference
            );

            activity()
                ->performedOn($repayment)
                ->causedBy(auth()->user())
                ->log('Processed loan payment: ' . $repayment->id);

            return redirect()->route('borrower.loans.show', $loan)
                ->with('success', 'Payment processed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    /**
     * Display loan application form.
     */
    public function loanApplicationForm()
    {
        $user = Auth::user();
        $client = $this->ensureClientExists($user);

        return view('borrower.loans.create', compact('client'));
    }

    /**
     * Submit loan application (Real-time workflow)
     */
    public function submitLoanApplication(Request $request)
    {
        $user = Auth::user();
        $client = $this->ensureClientExists($user);

        $request->validate([
            'amount' => 'required|numeric|min:1000|max:100000',
            'loan_purpose' => 'required|string|max:1000',
            'term_months' => 'required|integer|in:6,12,18,24,36',
            'monthly_income' => 'nullable|numeric|min:0',
            'employment_status' => 'nullable|string|in:employed,self_employed,business_owner,unemployed',
        ]);

        try {
            // Update client income if provided
            if ($request->monthly_income) {
                $client->update(['monthly_income' => $request->monthly_income]);
            }

            // Create loan application
            $loan = Loan::create([
                'client_id' => $client->id,
                'loan_number' => 'L' . now()->format('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'amount' => $request->amount,
                'principal_amount' => $request->amount,
                'outstanding_balance' => $request->amount,
                'loan_purpose' => $request->loan_purpose,
                'term_months' => $request->term_months, // Required field
                'loan_term' => $request->term_months, // Optional duplicate field
                'interest_rate' => 12, // Default rate, can be adjusted by loan officer
                'status' => 'pending', // Workflow: pending → under_review → approved → disbursed
                'application_date' => now(),
                'branch_id' => $user->branch_id ?? 1,
                'created_by' => $user->id,
            ]);

            // Log activity (optional - don't fail if this fails)
            try {
                activity()
                    ->performedOn($loan)
                    ->causedBy($user)
                    ->log("Borrower submitted loan application: {$loan->loan_number} for ${$request->amount}");
            } catch (\Exception $e) {
                \Log::warning('Failed to log activity: ' . $e->getMessage());
            }

            // Broadcast event for real-time updates (optional - don't fail if this fails)
            try {
                broadcast(new \App\Events\LoanApplicationSubmitted($loan))->toOthers();
            } catch (\Exception $e) {
                \Log::warning('Failed to broadcast event: ' . $e->getMessage());
            }

            // Send notification to loan officers (optional - don't fail if this fails)
            try {
                $loanOfficers = \App\Models\User::role('loan_officer')
                    ->where('branch_id', $loan->branch_id)
                    ->get();
                
                foreach ($loanOfficers as $officer) {
                    $officer->notify(new \App\Notifications\LoanApprovalNotification($loan));
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to send notifications: ' . $e->getMessage());
            }

            return redirect()->route('borrower.dashboard')
                ->with('success', 'Loan application submitted successfully! You will be notified when it\'s reviewed. Application #' . $loan->loan_number);
        } catch (\Exception $e) {
            \Log::error('Loan application submission failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error submitting application: ' . $e->getMessage());
        }
    }

    /**
     * Get real-time dashboard data (AJAX)
     */
    public function getRealtimeData(Request $request)
    {
        try {
            $user = Auth::user();
            $client = $this->ensureClientExists($user);

            // Get real-time data
            $loans = $client->loans()->with(['collateral', 'transactions'])->get();
            $savingsAccounts = $client->savingsAccounts()->get();
            $recentTransactions = $client->transactions()->latest()->limit(10)->get();
            
            $data = [
                'stats' => [
                    'active_loans' => $loans->whereIn('status', ['active', 'disbursed'])->count(),
                    'total_loan_amount' => $loans->whereIn('status', ['active', 'disbursed'])->sum('amount'),
                    'outstanding_balance' => $loans->whereIn('status', ['active', 'disbursed'])->sum('outstanding_balance'),
                    'savings_balance' => $savingsAccounts->sum('balance'),
                    'savings_accounts' => $savingsAccounts->count(),
                    'upcoming_payments' => $this->getUpcomingPaymentsCount($client),
                ],
                'recent_transactions' => $recentTransactions,
                'next_payment' => $this->getNextPayment($client),
                'timestamp' => now()->toISOString()
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get next payment due
     */
    private function getNextPayment($client)
    {
        $nextPayment = $client->loans()
            ->whereIn('status', ['active', 'disbursed'])
            ->where('next_due_date', '>=', now())
            ->orderBy('next_due_date', 'asc')
            ->first();

        if ($nextPayment) {
            return [
                'loan_id' => $nextPayment->id,
                'amount' => $nextPayment->next_payment_amount ?? 0,
                'due_date' => $nextPayment->next_due_date,
            ];
        }

        return null;
    }
}
