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
     * Display the borrower dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $client = $user->client;
        
        if (!$client) {
            return redirect()->route('borrower.profile')->with('error', 'Please complete your profile first.');
        }

        $loans = $client->loans()->with(['collaterals'])->get();
        $savingsAccounts = $client->savingsAccounts;
        $recentTransactions = $client->transactions()->latest()->limit(10)->get();

        return view('borrower.dashboard', compact('loans', 'savingsAccounts', 'recentTransactions'));
    }

    /**
     * Display borrower's loans.
     */
    public function loans()
    {
        $user = Auth::user();
        $client = $user->client;
        
        if (!$client) {
            return redirect()->route('borrower.profile')->with('error', 'Please complete your profile first.');
        }

        $loans = $client->loans()
            ->with(['collaterals', 'repayments'])
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
        
        $loan->load(['collaterals', 'repayments', 'transactions']);
        
        return view('borrower.loans.show', compact('loan'));
    }

    /**
     * Display borrower's savings accounts.
     */
    public function savings()
    {
        $user = Auth::user();
        $client = $user->client;
        
        if (!$client) {
            return redirect()->route('borrower.profile')->with('error', 'Please complete your profile first.');
        }

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
        $client = $user->client;
        
        if (!$client) {
            return redirect()->route('borrower.profile')->with('error', 'Please complete your profile first.');
        }

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
        $client = $user->client;

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
        ]);

        $user->update($request->only(['name', 'email', 'phone']));

        if ($user->client) {
            $user->client->update($request->only(['address']));
        }

        return redirect()->route('borrower.profile')->with('success', 'Profile updated successfully.');
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
        $client = $user->client;
        
        if (!$client) {
            return redirect()->route('borrower.profile')->with('error', 'Please complete your profile first.');
        }

        $loans = $client->loans()->where('status', 'disbursed')->get();

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
            $this->loanService->processRepayment(
                $loan, 
                $request->amount, 
                $request->payment_method
            );

            return redirect()->route('borrower.loans.show', $loan)
                ->with('success', 'Payment processed successfully.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Payment failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display loan application form.
     */
    public function loanApplicationForm()
    {
        $user = Auth::user();
        $client = $user->client;
        
        if (!$client) {
            return redirect()->route('borrower.profile')->with('error', 'Please complete your profile first.');
        }

        return view('borrower.loans.create');
    }

    /**
     * Submit loan application.
     */
    public function submitLoanApplication(Request $request)
    {
        $user = Auth::user();
        $client = $user->client;
        
        if (!$client) {
            return redirect()->route('borrower.profile')->with('error', 'Please complete your profile first.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1000|max:100000',
            'purpose' => 'required|string|max:500',
            'term_months' => 'required|integer|min:1|max:36',
        ]);

        $loan = Loan::create([
            'client_id' => $client->id,
            'amount' => $request->amount,
            'purpose' => $request->purpose,
            'term_months' => $request->term_months,
            'interest_rate' => 12, // Default rate
            'status' => 'pending',
            'outstanding_balance' => $request->amount,
        ]);

        return redirect()->route('borrower.loans.show', $loan)
            ->with('success', 'Loan application submitted successfully.');
    }
}