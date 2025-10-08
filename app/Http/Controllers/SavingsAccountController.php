<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SavingsAccount;
use App\Models\Client;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class SavingsAccountController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = SavingsAccount::with(['client', 'branch', 'createdBy'])
            ->when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });

        $savingsAccounts = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('savings-accounts.index', compact('savingsAccounts'));
    }

    public function create()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $clients = Client::when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->where('status', 'active')->get();

        $branches = $user->role === 'admin' ? \App\Models\Branch::all() : collect([$user->branch]);

        return view('savings-accounts.create', compact('clients', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'account_type' => 'required|in:regular,fixed_deposit,emergency',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'minimum_balance' => 'required|numeric|min:0',
            'branch_id' => 'required|exists:branches,id',
        ]);

        DB::beginTransaction();
        try {
            $savingsAccount = SavingsAccount::create([
                'account_number' => $this->generateAccountNumber(),
                'client_id' => $request->client_id,
                'account_type' => $request->account_type,
                'interest_rate' => $request->interest_rate,
                'balance' => $request->initial_deposit ?? 0,
                'minimum_balance' => $request->minimum_balance,
                'status' => 'active',
                'branch_id' => $request->branch_id,
                'created_by' => auth()->id(),
            ]);

            if ($request->initial_deposit > 0) {
                Transaction::create([
                    'transaction_number' => $this->generateTransactionNumber(),
                    'client_id' => $request->client_id,
                    'type' => 'deposit',
                    'amount' => $request->initial_deposit,
                    'description' => 'Initial deposit for savings account ' . $savingsAccount->account_number,
                    'balance_after' => $request->initial_deposit,
                    'branch_id' => $request->branch_id,
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();
            return redirect()->route('savings-accounts.show', $savingsAccount)
                ->with('success', 'Savings account created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Error creating savings account: ' . $e->getMessage());
        }
    }

    public function show(SavingsAccount $savingsAccount)
    {
        $savingsAccount->load(['client', 'branch', 'createdBy', 'transactions' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        return view('savings-accounts.show', compact('savingsAccount'));
    }

    public function edit(SavingsAccount $savingsAccount)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $clients = Client::when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->where('status', 'active')->get();

        $branches = $user->role === 'admin' ? \App\Models\Branch::all() : collect([$user->branch]);

        return view('savings-accounts.edit', compact('savingsAccount', 'clients', 'branches'));
    }

    public function update(Request $request, SavingsAccount $savingsAccount)
    {
        $request->validate([
            'account_type' => 'required|in:regular,fixed_deposit,emergency',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'minimum_balance' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive,suspended,closed',
        ]);

        $savingsAccount->update($request->all());

        return redirect()->route('savings-accounts.show', $savingsAccount)
            ->with('success', 'Savings account updated successfully.');
    }

    public function destroy(SavingsAccount $savingsAccount)
    {
        if ($savingsAccount->balance > 0) {
            return back()->with('error', 'Cannot delete savings account with remaining balance.');
        }

        $savingsAccount->delete();
        return redirect()->route('savings-accounts.index')
            ->with('success', 'Savings account deleted successfully.');
    }

    public function deposit(Request $request, SavingsAccount $savingsAccount)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $newBalance = $savingsAccount->balance + $request->amount;
            
            $savingsAccount->update(['balance' => $newBalance]);

            Transaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'client_id' => $savingsAccount->client_id,
                'savings_account_id' => $savingsAccount->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Deposit to savings account',
                'balance_after' => $newBalance,
                'branch_id' => $savingsAccount->branch_id,
                'created_by' => auth()->id(),
            ]);

            DB::commit();
            return back()->with('success', 'Deposit successful.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error processing deposit: ' . $e->getMessage());
        }
    }

    public function withdraw(Request $request, SavingsAccount $savingsAccount)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        if ($request->amount > $savingsAccount->balance) {
            return back()->with('error', 'Insufficient balance.');
        }

        $newBalance = $savingsAccount->balance - $request->amount;
        if ($newBalance < $savingsAccount->minimum_balance) {
            return back()->with('error', 'Withdrawal would result in balance below minimum required.');
        }

        DB::beginTransaction();
        try {
            $savingsAccount->update(['balance' => $newBalance]);

            Transaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'client_id' => $savingsAccount->client_id,
                'savings_account_id' => $savingsAccount->id,
                'type' => 'withdrawal',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Withdrawal from savings account',
                'balance_after' => $newBalance,
                'branch_id' => $savingsAccount->branch_id,
                'created_by' => auth()->id(),
            ]);

            DB::commit();
            return back()->with('success', 'Withdrawal successful.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error processing withdrawal: ' . $e->getMessage());
        }
    }

    public function close(Request $request, SavingsAccount $savingsAccount)
    {
        if ($savingsAccount->status === 'closed') {
            return back()->with('error', 'Account is already closed.');
        }

        if ($savingsAccount->balance > 0) {
            return back()->with('error', 'Cannot close account with non-zero balance. Please withdraw all funds first.');
        }

        $savingsAccount->update([
            'status' => 'closed',
            'closed_date' => now(),
        ]);

        return back()->with('success', 'Savings account closed successfully.');
    }

    private function generateAccountNumber()
    {
        $prefix = 'SAV';
        $timestamp = now()->format('Ymd');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return $prefix . $timestamp . $random;
    }

    public function fixedDeposits()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = SavingsAccount::with(['client', 'branch', 'createdBy'])
            ->where('account_type', 'fixed_deposit')
            ->when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });

        $fixedDeposits = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('savings-accounts.fixed-deposits', compact('fixedDeposits'));
    }

    private function generateTransactionNumber()
    {
        $prefix = 'TXN';
        $timestamp = now()->format('YmdHis');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        return $prefix . $timestamp . $random;
    }
}
