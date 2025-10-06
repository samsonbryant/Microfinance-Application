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
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:100|max:1000000',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'term_months' => 'required|integer|min:1|max:60',
            'purpose' => 'required|string|max:500',
            'collateral_description' => 'nullable|string|max:500',
            'collateral_value' => 'nullable|numeric|min:0',
            'branch_id' => 'required|exists:branches,id',
        ]);

        DB::beginTransaction();
        try {
            $loan = Loan::create([
                'loan_number' => $this->generateLoanNumber(),
                'client_id' => $request->client_id,
                'amount' => $request->amount,
                'interest_rate' => $request->interest_rate,
                'term_months' => $request->term_months,
                'purpose' => $request->purpose,
                'collateral_description' => $request->collateral_description,
                'collateral_value' => $request->collateral_value,
                'status' => 'pending',
                'branch_id' => $request->branch_id,
                'created_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('loans.show', $loan)
                ->with('success', 'Loan created successfully.');
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
