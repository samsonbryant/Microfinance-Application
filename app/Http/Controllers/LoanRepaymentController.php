<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanRepaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display loan repayments dashboard
     */
    public function index()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        // Base query with branch filtering
        $query = Loan::with(['client'])
            ->whereIn('status', ['active', 'overdue'])
            ->when($branchId && !$user->hasRole('admin'), function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });

        // Due today
        $dueToday = (clone $query)->whereDate('next_due_date', today())
            ->orderBy('next_due_date')
            ->get();

        // Overdue
        $overdue = (clone $query)->where('next_due_date', '<', now())
            ->orderBy('next_due_date')
            ->get();

        // Upcoming (next 30 days, excluding today)
        $upcoming = (clone $query)->whereBetween('next_due_date', [
                now()->addDay()->startOfDay(),
                now()->addDays(30)->endOfDay()
            ])
            ->orderBy('next_due_date')
            ->get();

        // All active loans
        $activeLoans = (clone $query)->orderBy('next_due_date')->paginate(20);

        return view('loan-repayments.index', compact('dueToday', 'overdue', 'upcoming', 'activeLoans'));
    }

    /**
     * Get real-time stats for AJAX updates
     */
    public function getStats()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = Loan::whereIn('status', ['active', 'overdue'])
            ->when($branchId && !$user->hasRole('admin'), function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });

        return response()->json([
            'success' => true,
            'due_today_count' => (clone $query)->whereDate('next_due_date', today())->count(),
            'overdue_count' => (clone $query)->where('next_due_date', '<', now())->count(),
            'active_count' => (clone $query)->count(),
        ]);
    }

    /**
     * Show repayment form for a specific loan
     */
    public function create()
    {
        $loanId = request('loan_id');
        
        if ($loanId) {
            $loan = Loan::with(['client', 'transactions'])->findOrFail($loanId);
            return view('loan-repayments.create', compact('loan'));
        }

        $loans = Loan::whereIn('status', ['active', 'overdue'])
            ->with('client')
            ->get();

        return view('loan-repayments.create', compact('loans'));
    }

    /**
     * Process a loan repayment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,cheque',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $loan = Loan::findOrFail($validated['loan_id']);

            // Ensure loan can accept payments
            if (!in_array($loan->status, ['active', 'overdue'])) {
                return back()->with('error', 'This loan cannot accept repayments.');
            }

            // Create transaction
            $transaction = Transaction::create([
                'transaction_number' => 'REP' . now()->format('Ymd') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
                'client_id' => $loan->client_id,
                'loan_id' => $loan->id,
                'type' => 'loan_repayment',
                'amount' => $validated['amount'],
                'description' => 'Loan repayment for ' . $loan->loan_number,
                'reference_number' => $validated['reference_number'] ?? null,
                'status' => 'completed',
                'branch_id' => $loan->branch_id,
                'created_by' => auth()->id(),
                'processed_at' => $validated['payment_date'],
            ]);

            // Update loan outstanding balance
            $newBalance = $loan->outstanding_balance - $validated['amount'];
            $loan->update([
                'outstanding_balance' => max($newBalance, 0),
                'total_paid' => ($loan->total_paid ?? 0) + $validated['amount'],
                'status' => $newBalance <= 0 ? 'completed' : $loan->status,
            ]);

            // Log activity
            activity()
                ->performedOn($loan)
                ->causedBy(auth()->user())
                ->log("Repayment of $" . number_format($validated['amount'], 2) . " recorded for loan {$loan->loan_number}");

            DB::commit();

            return redirect()->route('loan-repayments.index')
                ->with('success', 'Repayment recorded successfully! Transaction #' . $transaction->transaction_number);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Repayment error: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error processing repayment: ' . $e->getMessage());
        }
    }

    /**
     * Display a specific repayment
     */
    public function show($id)
    {
        $repayment = Transaction::with(['loan.client', 'client'])->findOrFail($id);
        
        // Check permissions
        $user = auth()->user();
        if (!$user->hasRole('admin') && $repayment->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized access.');
        }

        return view('loan-repayments.show', compact('repayment'));
    }
}
