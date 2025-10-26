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
    public function index(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = Transaction::with(['client', 'loan', 'savingsAccount', 'branch', 'createdBy'])
            ->when($branchId && !$user->hasRole('admin'), function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('reference_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('client', function($clientQuery) use ($search) {
                      $clientQuery->where('first_name', 'LIKE', "%{$search}%")
                                  ->orWhere('last_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

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
        $validated = $request->validate([
            'type' => 'required|in:deposit,withdrawal,transfer,loan_disbursement,loan_repayment,fee,penalty',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'client_id' => 'nullable|exists:clients,id',
            'loan_id' => 'nullable|exists:loans,id',
            'savings_account_id' => 'nullable|exists:savings_accounts,id',
            'reference_number' => 'nullable|string|max:100',
            'status' => 'required|in:pending,completed,failed',
        ]);

        try {
            DB::beginTransaction();

            $validated['transaction_number'] = 'TXN' . now()->format('Ymd') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $validated['branch_id'] = auth()->user()->branch_id ?? 1;
            $validated['created_by'] = auth()->id();
            $validated['processed_at'] = $validated['status'] === 'completed' ? now() : null;

            $transaction = Transaction::create($validated);

            // Log activity
            activity()
                ->performedOn($transaction)
                ->causedBy(auth()->user())
                ->log("Transaction created: {$transaction->transaction_number} - {$transaction->type} of $" . number_format($transaction->amount, 2));

            DB::commit();

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Transaction creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error creating transaction: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction)
    {
        // Check permission
        $user = auth()->user();
        if (!$user->hasRole('admin') && $transaction->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized access to transaction.');
        }

        $transaction->load(['client', 'loan', 'savingsAccount', 'branch', 'createdBy']);
        
        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(Transaction $transaction)
    {
        // Check permission
        $user = auth()->user();
        if (!$user->hasRole('admin') && $transaction->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized access to transaction.');
        }

        // Only allow editing pending transactions
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Only pending transactions can be edited.');
        }

        $clients = Client::where('status', 'active')->orderBy('first_name')->get();
        $loans = Loan::where('status', 'active')->with('client')->get();
        $savingsAccounts = SavingsAccount::where('status', 'active')->with('client')->get();

        return view('transactions.edit', compact('transaction', 'clients', 'loans', 'savingsAccounts'));
    }

    /**
     * Update the specified transaction in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        // Only allow updating pending transactions
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Only pending transactions can be updated.');
        }

        $validated = $request->validate([
            'type' => 'required|in:deposit,withdrawal,transfer,loan_disbursement,loan_repayment,fee,penalty',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'client_id' => 'nullable|exists:clients,id',
            'loan_id' => 'nullable|exists:loans,id',
            'savings_account_id' => 'nullable|exists:savings_accounts,id',
            'reference_number' => 'nullable|string|max:100',
        ]);

        try {
            $transaction->update($validated);

            // Log activity
            activity()
                ->performedOn($transaction)
                ->causedBy(auth()->user())
                ->log("Transaction updated: {$transaction->transaction_number}");

            return redirect()->route('transactions.show', $transaction)
                ->with('success', 'Transaction updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Transaction update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error updating transaction: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified transaction from storage.
     */
    public function destroy(Transaction $transaction)
    {
        try {
            // Only allow deleting pending transactions
            if ($transaction->status !== 'pending') {
                return back()->with('error', 'Only pending transactions can be deleted.');
            }

            $transactionNumber = $transaction->transaction_number;
            $transaction->delete();

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->log("Transaction deleted: {$transactionNumber}");

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Transaction deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Error deleting transaction: ' . $e->getMessage());
        }
    }

    /**
     * Approve a pending transaction
     */
    public function approve(Transaction $transaction)
    {
        try {
            if ($transaction->status !== 'pending') {
                return back()->with('error', 'Only pending transactions can be approved.');
            }

            $transaction->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            // Log activity
            activity()
                ->performedOn($transaction)
                ->causedBy(auth()->user())
                ->log("Transaction approved: {$transaction->transaction_number}");

            return back()->with('success', 'Transaction approved successfully!');

        } catch (\Exception $e) {
            \Log::error('Transaction approval failed: ' . $e->getMessage());
            return back()->with('error', 'Error approving transaction: ' . $e->getMessage());
        }
    }

    /**
     * Reverse a completed transaction
     */
    public function reverse(Transaction $transaction)
    {
        try {
            if ($transaction->status !== 'completed') {
                return back()->with('error', 'Only completed transactions can be reversed.');
            }

            DB::beginTransaction();

            // Create reversal transaction
            $reversalTransaction = Transaction::create([
                'transaction_number' => 'REV' . now()->format('Ymd') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
                'type' => $transaction->type . '_reversal',
                'amount' => $transaction->amount,
                'description' => 'Reversal of ' . $transaction->transaction_number . ' - ' . $transaction->description,
                'reference_number' => $transaction->transaction_number,
                'client_id' => $transaction->client_id,
                'loan_id' => $transaction->loan_id,
                'savings_account_id' => $transaction->savings_account_id,
                'branch_id' => $transaction->branch_id,
                'created_by' => auth()->id(),
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            // Update original transaction
            $transaction->update(['status' => 'reversed']);

            // Log activity
            activity()
                ->performedOn($transaction)
                ->causedBy(auth()->user())
                ->log("Transaction reversed: {$transaction->transaction_number}");

            DB::commit();

            return back()->with('success', 'Transaction reversed successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Transaction reversal failed: ' . $e->getMessage());
            return back()->with('error', 'Error reversing transaction: ' . $e->getMessage());
        }
    }

    /**
     * Get transactions for AJAX requests (real-time updates)
     */
    public function getTransactions(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = Transaction::with(['client', 'loan', 'savingsAccount', 'branch'])
            ->when($branchId && !$user->hasRole('admin'), function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('limit')) {
            $query->limit($request->limit);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'transactions' => $transactions->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'status' => $transaction->status,
                    'client_name' => $transaction->client ? $transaction->client->full_name : null,
                    'description' => $transaction->description,
                    'created_at' => $transaction->created_at->format('M d, Y H:i'),
                ];
            })
        ]);
    }
}