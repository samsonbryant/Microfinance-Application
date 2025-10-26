<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Client;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = Loan::with(['client', 'branch', 'createdBy'])
            ->when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });

        // Filter by status if provided
        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        $loans = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('loans.index', compact('loans'));
    }

    public function create()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $clients = Client::when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->where('status', 'active')->where('kyc_status', 'verified')->get();

        $branches = $user->role === 'admin' ? Branch::all() : collect([$user->branch]);

        return view('loans.create', compact('clients', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:100|max:10000000',
            'currency' => 'nullable|string|max:3',
            'release_date' => 'required|date',
            'term_months' => 'required|integer|min:1|max:120',
            'duration_period' => 'required|in:days,weeks,months,years',
            'interest_method' => 'required|in:flat,declining_balance,compound',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'interest_cycle' => 'required|in:once,daily,weekly,monthly,quarterly,yearly',
            'repayment_type' => 'required|in:standard,balloon,interest_only,custom',
            'repayment_cycle' => 'required|in:once,daily,weekly,biweekly,monthly,quarterly',
            'repayment_days' => 'nullable|array',
            'late_penalty_enabled' => 'nullable|boolean',
            'late_penalty_amount' => 'nullable|numeric|min:0',
            'late_penalty_type' => 'nullable|in:fixed,percentage',
            'collateral_name' => 'nullable|string|max:255',
            'collateral_description' => 'nullable|string',
            'collateral_defects' => 'nullable|string',
            'collateral_value' => 'nullable|numeric|min:0',
            'funding_account_id' => 'nullable|exists:chart_of_accounts,id',
            'loans_receivable_account_id' => 'nullable|exists:chart_of_accounts,id',
            'interest_income_account_id' => 'nullable|exists:chart_of_accounts,id',
            'fees_income_account_id' => 'nullable|exists:chart_of_accounts,id',
            'penalty_income_account_id' => 'nullable|exists:chart_of_accounts,id',
            'overpayment_account_id' => 'nullable|exists:chart_of_accounts,id',
            'credit_risk_score' => 'nullable|numeric|min:0|max:100',
            'branch_id' => 'required|exists:branches,id',
            'collateral_files.*' => 'nullable|file|mimes:jpeg,jpg,png,gif,pdf|max:10240',
            'loan_files.*' => 'nullable|file|mimes:jpeg,jpg,png,gif,pdf|max:10240',
            'fee_name.*' => 'nullable|string|max:255',
            'fee_type.*' => 'nullable|in:fixed,percentage',
            'fee_amount.*' => 'nullable|numeric|min:0',
            'fee_charge_type.*' => 'nullable|in:upfront,on_disbursement,on_repayment',
        ]);

        DB::beginTransaction();
        try {
            // Handle file uploads
            $loanFiles = [];
            if ($request->hasFile('loan_files')) {
                foreach ($request->file('loan_files') as $file) {
                    $loanFiles[] = $file->store('loan-files', 'public');
                }
            }

            // Create collateral if provided
            $collateralId = null;
            if ($request->collateral_name) {
                $collateralFiles = [];
                if ($request->hasFile('collateral_files')) {
                    foreach ($request->file('collateral_files') as $file) {
                        $collateralFiles[] = $file->store('collateral-files', 'public');
                    }
                }

                $collateral = Collateral::create([
                    'loan_id' => null, // Will update after loan creation
                    'type' => $validated['collateral_name'],
                    'description' => $validated['collateral_description'] ?? '',
                    'estimated_value' => $validated['collateral_value'] ?? 0,
                    'condition' => $validated['collateral_defects'] ?? 'Good',
                    'location' => '',
                    'ownership_proof' => !empty($collateralFiles) ? json_encode($collateralFiles) : null,
                    'status' => 'submitted',
                ]);
                $collateralId = $collateral->id;
            }

            // Create loan - Observer will calculate interest & schedule automatically
            $loan = Loan::create([
                'loan_number' => $this->generateLoanNumber(),
                'client_id' => $validated['client_id'],
                'branch_id' => $validated['branch_id'],
                'collateral_id' => $collateralId,
                'loan_type' => 'personal',
                'amount' => $validated['amount'],
                'principal_amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'USD',
                'interest_rate' => $validated['interest_rate'],
                'term_months' => $validated['term_months'],
                'loan_term' => $validated['term_months'],
                'duration_period' => $validated['duration_period'],
                'interest_method' => $validated['interest_method'],
                'interest_cycle' => $validated['interest_cycle'],
                'repayment_type' => $validated['repayment_type'],
                'repayment_cycle' => $validated['repayment_cycle'],
                'repayment_days' => $validated['repayment_days'] ?? [],
                'payment_frequency' => 'monthly',
                'disbursement_date' => null,
                'release_date' => $validated['release_date'],
                'application_date' => now(),
                'due_date' => now()->addMonths($validated['term_months']),
                'late_penalty_enabled' => $validated['late_penalty_enabled'] ?? false,
                'late_penalty_amount' => $validated['late_penalty_amount'] ?? 0,
                'late_penalty_type' => $validated['late_penalty_type'] ?? 'fixed',
                'funding_account_id' => $validated['funding_account_id'] ?? null,
                'loans_receivable_account_id' => $validated['loans_receivable_account_id'] ?? null,
                'interest_income_account_id' => $validated['interest_income_account_id'] ?? null,
                'fees_income_account_id' => $validated['fees_income_account_id'] ?? null,
                'penalty_income_account_id' => $validated['penalty_income_account_id'] ?? null,
                'overpayment_account_id' => $validated['overpayment_account_id'] ?? null,
                'files' => !empty($loanFiles) ? $loanFiles : null,
                'credit_risk_score' => $validated['credit_risk_score'] ?? null,
                'status' => 'pending', // Workflow: LO creates → BM reviews → Admin approves → Disburse
                'outstanding_balance' => 0, // Will be calculated by LoanCreationObserver
                'total_paid' => 0,
                'penalty_rate' => $validated['interest_rate'],
                'notes' => '',
                'created_by' => auth()->id(),
            ]);
            
            // LoanCreationObserver automatically:
            // - Calculates monthly_payment, total_interest, total_amount
            // - Sets outstanding_balance = principal + total_interest
            // - Generates full repayment_schedule (JSON)
            // - Sets next_due_date and next_payment_amount
            // - Broadcasts LoanApplicationSubmitted event

            // Update collateral with loan_id
            if ($collateralId) {
                Collateral::where('id', $collateralId)->update(['loan_id' => $loan->id]);
            }

            // Create loan fees
            if ($request->has('fee_name')) {
                foreach ($request->fee_name as $index => $feeName) {
                    if ($feeName) {
                        \App\Models\LoanFee::create([
                            'loan_id' => $loan->id,
                            'fee_name' => $feeName,
                            'fee_type' => $request->fee_type[$index] ?? 'fixed',
                            'fee_amount' => $request->fee_amount[$index] ?? 0,
                            'charge_type' => $request->fee_charge_type[$index] ?? 'upfront',
                            'is_recurring' => false,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('loans.show', $loan)
                ->with('success', 'Loan created successfully with all details.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Error creating loan: ' . $e->getMessage());
        }
    }

    public function show(Loan $loan)
    {
        $loan->load(['client', 'branch', 'createdBy', 'transactions' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        $stats = [
            'total_repayments' => $loan->transactions()->where('type', 'loan_repayment')->sum('amount'),
            'remaining_balance' => $loan->outstanding_balance,
            'next_payment_due' => $loan->getNextPaymentDue(),
            'days_overdue' => $loan->getDaysOverdue(),
        ];

        return view('loans.show', compact('loan', 'stats'));
    }

    public function edit(Loan $loan)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $clients = Client::when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->where('status', 'active')->where('kyc_status', 'verified')->get();

        $branches = $user->role === 'admin' ? Branch::all() : collect([$user->branch]);

        return view('loans.edit', compact('loan', 'clients', 'branches'));
    }

    public function update(Request $request, Loan $loan)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100|max:1000000',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'term_months' => 'required|integer|min:1|max:60',
            'purpose' => 'required|string|max:500',
            'collateral_description' => 'nullable|string|max:500',
            'collateral_value' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,approved,active,overdue,completed,defaulted',
        ]);

        $loan->update($request->all());

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan updated successfully.');
    }

    public function destroy(Loan $loan)
    {
        if ($loan->status === 'active' || $loan->status === 'overdue') {
            return back()->with('error', 'Cannot delete active or overdue loans.');
        }

        $loan->delete();
        return redirect()->route('loans.index')
            ->with('success', 'Loan deleted successfully.');
    }

    public function approve(Loan $loan)
    {
        try {
            // Use LoanCalculationService to calculate proper values
            $calculationService = app(\App\Services\LoanCalculationService::class);
            $schedule = $calculationService->calculateAmortizationSchedule(
                $loan->amount,
                $loan->interest_rate,
                $loan->term_months,
                now()
            );
            
            // Update loan with calculated values
            $updateData = [
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'monthly_payment' => $schedule['monthly_payment'],
                'total_interest' => $schedule['total_interest'],
                'total_amount' => $schedule['total_amount'],
                'outstanding_balance' => $schedule['total_amount'],
                'repayment_schedule' => json_encode($schedule['schedule']),
                'next_payment_amount' => $schedule['monthly_payment'],
            ];
            
            // Set next due date if schedule is available
            if (!empty($schedule['schedule']) && isset($schedule['schedule'][0]['due_date'])) {
                $updateData['next_due_date'] = $schedule['schedule'][0]['due_date'];
            }
            
            $loan->update($updateData);
            
            // Refresh the loan model to ensure all casts are applied
            $loan->refresh();
            
            // Log activity
            activity()
                ->performedOn($loan)
                ->causedBy(auth()->user())
                ->log("Loan approved: {$loan->loan_number}");

            return back()->with('success', 'Loan approved successfully.');
        } catch (\Exception $e) {
            \Log::error('Loan approval failed: ' . $e->getMessage());
            return back()->with('error', 'Error approving loan: ' . $e->getMessage());
        }
    }

    public function disburse(Loan $loan)
    {
        // Both Admin and Branch Manager can disburse
        if (!auth()->user()->hasAnyRole(['admin', 'branch_manager'])) {
            return back()->with('error', 'You do not have permission to disburse loans.');
        }

        if ($loan->status !== 'approved') {
            return back()->with('error', 'Only approved loans can be disbursed.');
        }

        DB::beginTransaction();
        try {
            // Update loan status to active (ready for repayments)
            $loan->update([
                'status' => 'active', // Active means disbursed and ready for repayments
                'disbursement_date' => now(),
                'disbursed_by' => auth()->id(),
            ]);
            
            // LoanObserver will automatically:
            // - Create Transfer entry (Bank → Loan Portfolio)
            // - Create Processing Fee Revenue entry
            // - Post both to General Ledger
            // - Update account balances
            // - Broadcast LoanDisbursed event
            // - Send notifications

            // Create disbursement transaction for tracking
            \App\Models\Transaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'client_id' => $loan->client_id,
                'loan_id' => $loan->id,
                'type' => 'loan_disbursement',
                'amount' => $loan->amount,
                'description' => "Loan disbursement for {$loan->loan_number}",
                'balance_after' => $loan->amount,
                'status' => 'completed',
                'branch_id' => $loan->branch_id,
                'created_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            activity()
                ->performedOn($loan)
                ->causedBy(auth()->user())
                ->log("Disbursed loan {$loan->loan_number} - Amount: ${$loan->amount}");

            DB::commit();
            
            return back()->with('success', 'Loan disbursed successfully! Accounting entries created automatically.');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Loan disbursement error: ' . $e->getMessage());
            return back()->with('error', 'Error disbursing loan: ' . $e->getMessage());
        }
    }

    public function reject(Loan $loan)
    {
        if ($loan->status !== 'pending') {
            return back()->with('error', 'Only pending loans can be rejected.');
        }

        $loan->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
        ]);

        return back()->with('success', 'Loan rejected successfully.');
    }

    public function repay(Loan $loan)
    {
        if (!in_array($loan->status, ['active', 'overdue'])) {
            return back()->with('error', 'Only active or overdue loans can accept repayments.');
        }

        return redirect()->route('loan-repayments.create', ['loan_id' => $loan->id]);
    }

    private function generateLoanNumber()
    {
        $prefix = 'LOAN';
        $timestamp = now()->format('Ymd');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return $prefix . $timestamp . $random;
    }

    private function generateTransactionNumber()
    {
        $prefix = 'TXN';
        $timestamp = now()->format('YmdHis');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        return $prefix . $timestamp . $random;
    }

    /**
     * Display payment schedule for a loan
     */
    public function paymentSchedule($id)
    {
        try {
            $loan = Loan::with('client')->findOrFail($id);
            
            // Check if user has permission to view this loan
            $user = auth()->user();
            if (!$user->hasRole('admin') && $loan->branch_id !== $user->branch_id) {
                abort(403, 'Unauthorized access to loan.');
            }
            
            // Get payment schedule
            $schedule = [];
            if ($loan->repayment_schedule) {
                $schedule = json_decode($loan->repayment_schedule, true);
                
                // Update status based on current date and payments made
                foreach ($schedule as &$payment) {
                    $dueDate = \Carbon\Carbon::parse($payment['due_date']);
                    $today = now();
                    
                    if ($payment['status'] === 'pending') {
                        if ($dueDate->isBefore($today)) {
                            $payment['status'] = 'overdue';
                        } elseif ($dueDate->isToday()) {
                            $payment['status'] = 'due';
                        }
                    }
                }
            }
            
            return view('loans.payment-schedule', compact('loan', 'schedule'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading payment schedule: ' . $e->getMessage());
        }
    }

    /**
     * Generate payment schedule for a loan
     */
    public function generateSchedule($id)
    {
        try {
            $loan = Loan::findOrFail($id);
            
            // Only admin and loan officers can generate schedules
            if (!auth()->user()->hasRole(['admin', 'loan_officer'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to generate payment schedule'
                ], 403);
            }
            
            // Use LoanCalculationService to generate schedule
            $calculationService = app(\App\Services\LoanCalculationService::class);
            $scheduleData = $calculationService->calculateAmortizationSchedule(
                $loan->amount,
                $loan->interest_rate,
                $loan->term_months,
                $loan->disbursement_date ?? now()
            );
            
            // Update loan with calculated values
            $loan->update([
                'monthly_payment' => $scheduleData['monthly_payment'],
                'total_interest' => $scheduleData['total_interest'],
                'total_amount' => $scheduleData['total_amount'],
                'outstanding_balance' => $scheduleData['total_amount'],
                'repayment_schedule' => json_encode($scheduleData['schedule']),
                'next_due_date' => $scheduleData['schedule'][0]['due_date'] ?? null,
                'next_payment_amount' => $scheduleData['monthly_payment'],
            ]);
            
            // Log activity
            activity()
                ->performedOn($loan)
                ->causedBy(auth()->user())
                ->log("Payment schedule generated for loan: {$loan->loan_number}");
            
            return response()->json([
                'success' => true,
                'message' => 'Payment schedule generated successfully!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Schedule generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print payment schedule
     */
    public function printSchedule($id)
    {
        return redirect()->route('loans.payment-schedule', ['id' => $id, 'print' => 'true']);
    }

    /**
     * Show repayment form
     */
    public function repaymentForm($id)
    {
        try {
            $loan = Loan::with(['client', 'transactions'])->findOrFail($id);
            
            // Check if user has permission
            $user = auth()->user();
            if (!$user->hasRole('admin') && $loan->branch_id !== $user->branch_id) {
                abort(403, 'Unauthorized access to loan.');
            }
            
            // Only allow repayments for active or overdue loans
            if (!in_array($loan->status, ['active', 'overdue'])) {
                return back()->with('error', 'Repayments can only be made on active or overdue loans.');
            }
            
            return view('loans.repayment', compact('loan'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading repayment form: ' . $e->getMessage());
        }
    }

    /**
     * Process loan repayment
     */
    public function processRepayment(Request $request, $id)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,check',
            'payment_date' => 'required|date|before_or_equal:today',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();
            
            $loan = Loan::findOrFail($id);
            
            // Validate payment amount
            if ($request->payment_amount > $loan->outstanding_balance) {
                return back()->with('error', 'Payment amount cannot exceed outstanding balance.');
            }
            
            // Calculate payment breakdown
            $paymentAmount = $request->payment_amount;
            $outstandingBalance = $loan->outstanding_balance;
            
            // Simple interest calculation (should be enhanced with proper amortization)
            $monthlyRate = ($loan->interest_rate / 100) / 12;
            $interestDue = $outstandingBalance * $monthlyRate;
            
            $interestPayment = min($paymentAmount, $interestDue);
            $principalPayment = $paymentAmount - $interestPayment;
            $penaltyPayment = 0; // Can be calculated based on overdue days
            
            // Create transaction record
            $transaction = $loan->transactions()->create([
                'transaction_number' => $this->generateTransactionNumber(),
                'type' => 'loan_repayment',
                'amount' => $paymentAmount,
                'principal_amount' => $principalPayment,
                'interest_amount' => $interestPayment,
                'penalty_amount' => $penaltyPayment,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'transaction_date' => $request->payment_date,
                'description' => "Loan repayment for {$loan->loan_number}",
                'notes' => $request->notes,
                'status' => 'completed',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'balance_before' => $outstandingBalance,
                'balance_after' => $outstandingBalance - $principalPayment,
                'branch_id' => $loan->branch_id,
            ]);
            
            // Update loan balance and status
            $newOutstandingBalance = $outstandingBalance - $principalPayment;
            $totalPaid = $loan->total_paid + $paymentAmount;
            
            $loan->update([
                'outstanding_balance' => $newOutstandingBalance,
                'total_paid' => $totalPaid,
                'status' => $newOutstandingBalance <= 0.01 ? 'completed' : 
                           ($loan->next_due_date && $loan->next_due_date < now() ? 'overdue' : 'active'),
                'last_payment_date' => $request->payment_date,
                'last_payment_amount' => $paymentAmount,
            ]);
            
            // Update repayment schedule if exists
            if ($loan->repayment_schedule) {
                $schedule = json_decode($loan->repayment_schedule, true);
                
                // Mark payments as paid in schedule
                $remainingPayment = $principalPayment;
                foreach ($schedule as &$payment) {
                    if ($payment['status'] === 'pending' && $remainingPayment > 0) {
                        if ($remainingPayment >= $payment['principal']) {
                            $payment['status'] = 'paid';
                            $payment['paid_date'] = $request->payment_date;
                            $remainingPayment -= $payment['principal'];
                        } else {
                            // Partial payment - split the installment
                            $payment['principal_paid'] = $remainingPayment;
                            $payment['principal'] -= $remainingPayment;
                            $remainingPayment = 0;
                            break;
                        }
                    }
                }
                
                $loan->update(['repayment_schedule' => json_encode($schedule)]);
            }
            
            // Create accounting entries
            $this->createRepaymentAccountingEntries($loan, $transaction);
            
            // Log activity
            activity()
                ->performedOn($loan)
                ->causedBy(auth()->user())
                ->withProperties([
                    'transaction_id' => $transaction->id,
                    'amount' => $paymentAmount,
                    'payment_method' => $request->payment_method,
                    'principal' => $principalPayment,
                    'interest' => $interestPayment,
                ])
                ->log("Loan repayment processed: {$loan->loan_number} - Amount: \${$paymentAmount}");
            
            DB::commit();
            
            // Broadcast real-time update
            try {
                broadcast(new \App\Events\LoanRepaymentProcessed($loan, $transaction))->toOthers();
            } catch (\Exception $e) {
                \Log::warning('Failed to broadcast repayment event: ' . $e->getMessage());
            }
            
            return redirect()->route('loans.show', $loan->id)
                ->with('success', "Payment of \${$paymentAmount} processed successfully! New balance: \${$newOutstandingBalance}");
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Loan repayment error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }
    
    /**
     * Create accounting entries for loan repayment
     */
    private function createRepaymentAccountingEntries($loan, $transaction)
    {
        // Debit Cash Account (or selected payment account)
        \App\Models\GeneralLedgerEntry::create([
            'account_id' => $loan->funding_account_id ?? 1, // Default to cash account
            'transaction_id' => $transaction->id,
            'transaction_date' => $transaction->transaction_date,
            'description' => "Loan repayment - {$loan->loan_number}",
            'debit' => $transaction->amount,
            'credit' => 0,
            'balance_after' => 0, // Will be calculated
            'reference_number' => $transaction->reference_number,
            'status' => 'approved',
            'branch_id' => $loan->branch_id,
            'created_by' => auth()->id(),
        ]);
        
        // Credit Loans Receivable (Principal portion)
        if ($transaction->principal_amount > 0) {
            \App\Models\GeneralLedgerEntry::create([
                'account_id' => $loan->loans_receivable_account_id ?? 2, // Default to loans receivable
                'transaction_id' => $transaction->id,
                'transaction_date' => $transaction->transaction_date,
                'description' => "Loan principal repayment - {$loan->loan_number}",
                'debit' => 0,
                'credit' => $transaction->principal_amount,
                'balance_after' => 0, // Will be calculated
                'reference_number' => $transaction->reference_number,
                'status' => 'approved',
                'branch_id' => $loan->branch_id,
                'created_by' => auth()->id(),
            ]);
        }
        
        // Credit Interest Income (Interest portion)
        if ($transaction->interest_amount > 0) {
            \App\Models\GeneralLedgerEntry::create([
                'account_id' => $loan->interest_income_account_id ?? 3, // Default to interest income
                'transaction_id' => $transaction->id,
                'transaction_date' => $transaction->transaction_date,
                'description' => "Interest payment - {$loan->loan_number}",
                'debit' => 0,
                'credit' => $transaction->interest_amount,
                'balance_after' => 0, // Will be calculated
                'reference_number' => $transaction->reference_number,
                'status' => 'approved',
                'branch_id' => $loan->branch_id,
                'created_by' => auth()->id(),
            ]);
        }
    }
}
