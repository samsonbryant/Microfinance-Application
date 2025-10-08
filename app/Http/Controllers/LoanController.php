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

            // Calculate outstanding balance
            $outstandingBalance = $validated['amount'];
            
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
                'duration_period' => $validated['duration_period'],
                'interest_method' => $validated['interest_method'],
                'interest_cycle' => $validated['interest_cycle'],
                'repayment_type' => $validated['repayment_type'],
                'repayment_cycle' => $validated['repayment_cycle'],
                'repayment_days' => $validated['repayment_days'] ?? [],
                'payment_frequency' => 'monthly',
                'disbursement_date' => null,
                'release_date' => $validated['release_date'],
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
                'status' => 'pending',
                'outstanding_balance' => $outstandingBalance,
                'total_paid' => 0,
                'penalty_rate' => $validated['interest_rate'],
                'notes' => '',
                'created_by' => auth()->id(),
            ]);

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
        $loan->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Loan approved successfully.');
    }

    public function disburse(Loan $loan)
    {
        if ($loan->status !== 'approved') {
            return back()->with('error', 'Only approved loans can be disbursed.');
        }

        DB::beginTransaction();
        try {
            $loan->update([
                'status' => 'active',
                'disbursement_date' => now(),
                'disbursed_by' => auth()->id(),
            ]);

            // Create disbursement transaction
            \App\Models\Transaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'client_id' => $loan->client_id,
                'loan_id' => $loan->id,
                'type' => 'loan_disbursement',
                'amount' => $loan->amount,
                'description' => 'Loan disbursement for ' . $loan->loan_number,
                'balance_after' => $loan->amount,
                'status' => 'approved',
                'branch_id' => $loan->branch_id,
                'created_by' => auth()->id(),
            ]);

            DB::commit();
            return back()->with('success', 'Loan disbursed successfully.');
        } catch (\Exception $e) {
            DB::rollback();
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
}
