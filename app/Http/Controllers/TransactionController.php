<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Client;
use App\Models\Loan;
use App\Models\SavingsAccount;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = Transaction::with(['client', 'loan', 'savingsAccount', 'branch', 'createdBy'])
            ->when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $clients = Client::when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->where('status', 'active')->get();

        $loans = Loan::when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->where('status', 'active')->get();

        $savingsAccounts = SavingsAccount::when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->where('status', 'active')->get();

        $branches = $user->role === 'admin' ? \App\Models\Branch::all() : collect([$user->branch]);

        return view('transactions.create', compact('clients', 'loans', 'savingsAccounts', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'type' => 'required|in:deposit,withdrawal,loan_disbursement,loan_repayment,transfer,fee',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'loan_id' => 'nullable|exists:loans,id',
            'savings_account_id' => 'nullable|exists:savings_accounts,id',
        ]);

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'client_id' => $request->client_id,
                'loan_id' => $request->loan_id,
                'savings_account_id' => $request->savings_account_id,
                'type' => $request->type,
                'amount' => $request->amount,
                'description' => $request->description,
                'balance_after' => $request->amount, // This would be calculated based on account balance
                'status' => 'pending',
                'branch_id' => $request->branch_id,
                'created_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('transactions.show', $transaction)
                ->with('success', 'Transaction created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Error creating transaction: ' . $e->getMessage());
        }
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['client', 'loan', 'savingsAccount', 'branch', 'createdBy']);
        return view('transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $clients = Client::when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->where('status', 'active')->get();

        $loans = Loan::when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->where('status', 'active')->get();

        $savingsAccounts = SavingsAccount::when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->where('status', 'active')->get();

        $branches = $user->role === 'admin' ? \App\Models\Branch::all() : collect([$user->branch]);

        return view('transactions.edit', compact('transaction', 'clients', 'loans', 'savingsAccounts', 'branches'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'type' => 'required|in:deposit,withdrawal,loan_disbursement,loan_repayment,transfer,fee',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'status' => 'required|in:pending,approved,rejected,reversed',
        ]);

        $transaction->update($request->all());

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->status === 'approved') {
            return back()->with('error', 'Cannot delete approved transactions.');
        }

        $transaction->delete();
        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }

    public function approve(Transaction $transaction)
    {
        $transaction->update(['status' => 'approved']);
        return back()->with('success', 'Transaction approved successfully.');
    }

    public function reverse(Transaction $transaction)
    {
        $transaction->update(['status' => 'reversed']);
        return back()->with('success', 'Transaction reversed successfully.');
    }

    private function generateTransactionNumber()
    {
        $prefix = 'TXN';
        $timestamp = now()->format('YmdHis');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        return $prefix . $timestamp . $random;
    }
}
