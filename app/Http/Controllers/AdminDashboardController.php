<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\SavingsAccount;
use App\Models\Client;
use App\Models\User;
use App\Models\Branch;
use App\Services\RealtimeDashboardService;

class AdminDashboardController extends Controller
{
    protected $realtimeService;

    public function __construct(RealtimeDashboardService $realtimeService)
    {
        $this->realtimeService = $realtimeService;
    }

    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        try {
            // Get comprehensive analytics data
            $analyticsService = app(\App\Services\FinancialAnalyticsService::class);
            $analytics = $analyticsService->getComprehensiveAnalytics();
            
            // Get additional dashboard data
            $data = $this->realtimeService->getDashboardData();
            
            // Merge analytics with dashboard data
            $dashboardData = array_merge($data, ['analytics' => $analytics]);
            
            return view('admin.dashboard', compact('analytics', 'data'));
        } catch (\Exception $e) {
            \Log::error('Admin Dashboard Error: ' . $e->getMessage());
            
            // Return view with empty data on error
            $analytics = $this->getEmptyAnalytics();
            $data = [];
            
            return view('admin.dashboard', compact('analytics', 'data'))
                ->with('error', 'Unable to load dashboard data. Please try again.');
        }
    }

    /**
     * Approve loan application
     */
    public function approveLoan(Request $request, $loanId)
    {
        try {
            $loan = \App\Models\Loan::findOrFail($loanId);
            
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
            
            return response()->json([
                'success' => true,
                'message' => 'Loan approved successfully!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Loan approval failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving loan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reject loan application
     */
    public function rejectLoan(Request $request, $loanId)
    {
        try {
            $loan = \App\Models\Loan::findOrFail($loanId);
            
            $loan->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $request->reason ?? 'No reason provided',
            ]);
            
            // Log activity
            activity()
                ->performedOn($loan)
                ->causedBy(auth()->user())
                ->log("Loan rejected: {$loan->loan_number} - Reason: " . ($request->reason ?? 'No reason provided'));
            
            return response()->json([
                'success' => true,
                'message' => 'Loan rejected successfully!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Loan rejection failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting loan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get pending approvals
     */
    public function getPendingApprovals()
    {
        try {
            $pendingLoans = \App\Models\Loan::with('client')
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($loan) {
                    return [
                        'id' => $loan->id,
                        'client_name' => $loan->client->first_name . ' ' . $loan->client->last_name,
                        'amount' => $loan->amount,
                        'created_at' => $loan->created_at->diffForHumans(),
                    ];
                });
            
            return response()->json([
                'success' => true,
                'approvals' => $pendingLoans,
                'count' => $pendingLoans->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching pending approvals'
            ], 500);
        }
    }
    
    /**
     * Get live feed data
     */
    public function getLiveFeed()
    {
        try {
            $recentActivities = \App\Models\Loan::with('client')
                ->orderBy('created_at', 'desc')
                ->limit(15)
                ->get()
                ->map(function ($loan) {
                    return [
                        'id' => $loan->id,
                        'client_name' => $loan->client->first_name . ' ' . $loan->client->last_name,
                        'amount' => $loan->amount,
                        'status' => $loan->status,
                        'created_at' => $loan->created_at->diffForHumans(),
                    ];
                });
            
            return response()->json([
                'success' => true,
                'activities' => $recentActivities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching live feed'
            ], 500);
        }
    }

    /**
     * Get real-time dashboard data.
     */
    public function getRealtimeData(Request $request)
    {
        try {
            // Get comprehensive analytics data
            $analyticsService = app(\App\Services\FinancialAnalyticsService::class);
            $analytics = $analyticsService->getComprehensiveAnalytics();
            
            // Get dashboard data
            $data = $this->realtimeService->getDashboardData();
            
            return response()->json([
                'success' => true,
                'data' => array_merge($data, $analytics),
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export dashboard data.
     */
    public function export(Request $request)
    {
        try {
            $data = $this->realtimeService->getDashboardData();
            
            // Implement export logic here
            return response()->json([
                'success' => true,
                'message' => 'Export functionality will be implemented'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting data: ' . $e->getMessage()
            ], 500);
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
