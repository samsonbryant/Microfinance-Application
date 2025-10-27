<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\SavingsAccount;
use App\Models\Client;
use App\Models\User;
use App\Models\Branch;
use App\Services\RealtimeDashboardService;

class BranchManagerDashboardController extends Controller
{
    protected $realtimeService;

    public function __construct(RealtimeDashboardService $realtimeService)
    {
        $this->realtimeService = $realtimeService;
    }

    /**
     * Display the branch manager dashboard.
     */
    public function index()
    {
        try {
            $branchId = auth()->user()->branch_id;
            
            // Get comprehensive analytics data for branch
            $analyticsService = app(\App\Services\FinancialAnalyticsService::class);
            $analytics = $analyticsService->getComprehensiveAnalytics($branchId);
            
            // Get branch dashboard data
            $data = $this->realtimeService->getBranchData($branchId);
            
            return view('branch-manager.dashboard', compact('analytics', 'data'));
        } catch (\Exception $e) {
            \Log::error('Branch Manager Dashboard Error: ' . $e->getMessage());
            
            // Return view with empty data on error
            $analytics = $this->getEmptyAnalytics();
            $data = [];
            
            return view('branch-manager.dashboard', compact('analytics', 'data'))
                ->with('error', 'Unable to load dashboard data. Please try again.');
        }
    }

    /**
     * Get real-time branch data.
     */
    public function getRealtimeData(Request $request)
    {
        try {
            $branchId = auth()->user()->branch_id;
            
            // Get comprehensive analytics data for branch
            $analyticsService = app(\App\Services\FinancialAnalyticsService::class);
            $analytics = $analyticsService->getComprehensiveAnalytics($branchId);
            
            // Get branch dashboard data
            $data = $this->realtimeService->getBranchData($branchId);
            
            return response()->json([
                'success' => true,
                'data' => array_merge($data, $analytics),
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching branch data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export branch report.
     */
    public function exportReport(Request $request)
    {
        try {
            $branchId = auth()->user()->branch_id;
            $data = $this->realtimeService->getBranchData($branchId);
            
            // Implement branch report export logic here
            return response()->json([
                'success' => true,
                'message' => 'Branch report export functionality will be implemented'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting branch report: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display collections page for branch manager
     */
    public function collections()
    {
        return view('branch-manager.collections');
    }

    /**
     * Process payment from branch manager
     */
    public function processPayment(Request $request)
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
            $branchId = auth()->user()->branch_id;

            // Verify loan belongs to branch manager's branch
            if ($loan->branch_id !== $branchId) {
                return back()->with('error', 'Unauthorized access to this loan.');
            }

            // Ensure loan can accept payments
            if (!in_array($loan->status, ['active', 'overdue'])) {
                return back()->with('error', 'This loan cannot accept repayments.');
            }

            // Create transaction
            $transaction = \App\Models\Transaction::create([
                'transaction_number' => 'REP' . now()->format('Ymd') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
                'client_id' => $loan->client_id,
                'loan_id' => $loan->id,
                'type' => 'loan_repayment',
                'amount' => $validated['amount'],
                'description' => 'Loan repayment for ' . $loan->loan_number . ($validated['notes'] ? ' - ' . $validated['notes'] : ''),
                'reference_number' => $validated['reference_number'] ?? null,
                'status' => 'completed',
                'branch_id' => $loan->branch_id,
                'created_by' => auth()->id(),
                'processed_at' => $validated['payment_date'],
            ]);

            // Calculate new balance
            $newBalance = $loan->outstanding_balance - $validated['amount'];
            
            // Update loan
            $loan->update([
                'outstanding_balance' => max($newBalance, 0),
                'total_paid' => ($loan->total_paid ?? 0) + $validated['amount'],
                'status' => $newBalance <= 0 ? 'completed' : ($loan->status === 'overdue' && $newBalance < $loan->outstanding_balance ? 'active' : $loan->status),
                'last_payment_date' => $validated['payment_date'],
            ]);

            // Update next due date if loan is completed
            if ($newBalance <= 0) {
                $loan->update(['next_due_date' => null]);
            } elseif ($loan->next_due_date && $loan->next_due_date < now()) {
                // Calculate next payment date based on loan term
                $loan->update([
                    'next_due_date' => now()->addMonth()
                ]);
            }

            // Log activity
            activity()
                ->performedOn($loan)
                ->causedBy(auth()->user())
                ->log("Repayment of $" . number_format($validated['amount'], 2) . " recorded for loan {$loan->loan_number} by Branch Manager");

            DB::commit();

            return back()->with('success', 'Payment processed successfully! Transaction #' . $transaction->transaction_number);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Branch Manager Payment processing error: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }
    
    /**
     * Get empty analytics structure for fallback
     */
    private function getEmptyAnalytics()
    {
        return [
            'loans_due_today' => ['count' => 0, 'amount' => 0, 'loans' => []],
            'overdue_loans' => ['count' => 0, 'amount' => 0, 'percentage' => 0, 'loans' => []],
            'active_loans' => ['count' => 0, 'amount' => 0, 'outstanding' => 0, 'percentage' => 0],
            'loan_requests' => ['count' => 0, 'amount' => 0, 'percentage' => 0],
            'released_principal' => ['total' => 0, 'this_month' => 0, 'last_month' => 0],
            'outstanding_principal' => ['total' => 0, 'active_loans' => 0, 'overdue_loans' => 0],
            'portfolio_at_risk' => [
                '14_day_par' => ['amount' => 0, 'percentage' => 0],
                '30_day_par' => ['amount' => 0, 'percentage' => 0],
                'over_30_day_par' => ['amount' => 0, 'percentage' => 0],
                'total_par' => ['amount' => 0, 'percentage' => 0]
            ],
            'interest_collected' => ['total' => 0, 'this_month' => 0, 'last_month' => 0],
            'realized_profit' => ['total' => 0, 'interest' => 0, 'fees' => 0, 'penalties' => 0],
            'active_borrowers' => ['count' => 0, 'total_clients' => 0, 'percentage' => 0],
            'default_rate' => ['count' => 0, 'percentage' => 0],
            'charged_fees' => ['total' => 0, 'this_month' => 0, 'last_month' => 0],
            'penalties_collected' => ['total' => 0, 'this_month' => 0, 'last_month' => 0],
            'pending_loans' => ['count' => 0, 'amount' => 0, 'percentage' => 0],
            'repayments_collected' => ['total' => 0, 'this_month' => 0, 'last_month' => 0],
            'average_loan_size' => ['average' => 0, 'median' => 0, 'min' => 0, 'max' => 0],
            'expected_profit' => ['total' => 0, 'monthly' => 0],
            'loan_release_vs_completed' => ['released' => 0, 'completed' => 0, 'defaulted' => 0, 'completion_rate' => 0, 'default_rate' => 0],
            'monthly_trends' => []
        ];
    }
}
