<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanRepaymentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = Transaction::where('type', 'loan_repayment')
            ->with(['loan.client', 'client', 'branch'])
            ->when($branchId && !$user->hasRole('admin'), function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });

        // Filter by status
        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        $repayments = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('loan-repayments.index', compact('repayments'));
    }

    public function create()
    {
        $loans = Loan::where('status', 'active')
            ->with('client')
            ->get();

        return view('loan-repayments.create', compact('loans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,check,mobile_money',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $loan = Loan::findOrFail($validated['loan_id']);

        DB::beginTransaction();
        try {
            // Create transaction
            $transaction = Transaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'type' => 'loan_repayment',
                'amount' => $validated['amount'],
                'status' => 'completed',
                'client_id' => $loan->client_id,
                'loan_id' => $loan->id,
                'branch_id' => $loan->branch_id,
                'reference_number' => $validated['reference_number'] ?? null,
                'description' => 'Loan repayment for ' . $loan->loan_number,
                'created_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            // Update loan balance
            $loan->outstanding_balance -= $validated['amount'];
            $loan->total_paid += $validated['amount'];

            if ($loan->outstanding_balance <= 0) {
                $loan->status = 'completed';
                $loan->outstanding_balance = 0;
            }

            $loan->save();

            DB::commit();

            return redirect()->route('loan-repayments.index')
                ->with('success', 'Loan repayment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error processing repayment: ' . $e->getMessage());
        }
    }

    public function show(Transaction $loanRepayment)
    {
        $loanRepayment->load(['loan.client', 'client', 'branch', 'createdBy']);
        return view('loan-repayments.show', compact('loanRepayment'));
    }

    public function edit(Transaction $loanRepayment)
    {
        return view('loan-repayments.edit', compact('loanRepayment'));
    }

    public function update(Request $request, Transaction $loanRepayment)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $loanRepayment->update($validated);

        return redirect()->route('loan-repayments.index')
            ->with('success', 'Loan repayment updated successfully.');
    }

    public function destroy(Transaction $loanRepayment)
    {
        $loanRepayment->delete();

        return redirect()->route('loan-repayments.index')
            ->with('success', 'Loan repayment deleted successfully.');
    }

    private function generateTransactionNumber()
    {
        $prefix = 'RPT';
        $timestamp = now()->format('YmdHis');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        return $prefix . $timestamp . $random;
    }
}
