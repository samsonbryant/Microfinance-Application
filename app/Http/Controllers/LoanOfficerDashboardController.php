<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\SavingsAccount;
use App\Models\Client;
use App\Models\User;
use App\Models\Branch;
use App\Services\RealtimeDashboardService;

class LoanOfficerDashboardController extends Controller
{
    protected $realtimeService;

    public function __construct(RealtimeDashboardService $realtimeService)
    {
        $this->realtimeService = $realtimeService;
    }

    /**
     * Display the loan officer dashboard.
     */
    public function index()
    {
        try {
            $userId = auth()->id();
            $branchId = auth()->user()->branch_id;
            
            // Get comprehensive analytics data for user
            $analyticsService = app(\App\Services\FinancialAnalyticsService::class);
            $analytics = $analyticsService->getComprehensiveAnalytics($branchId, $userId);
            
            // Get user dashboard data
            $data = $this->realtimeService->getUserData($userId);
            
            return view('loan-officer.dashboard', compact('analytics', 'data'));
        } catch (\Exception $e) {
            \Log::error('Loan Officer Dashboard Error: ' . $e->getMessage());
            
            // Return view with empty data on error
            $analytics = $this->getEmptyAnalytics();
            $data = [];
            
            return view('loan-officer.dashboard', compact('analytics', 'data'))
                ->with('error', 'Unable to load dashboard data. Please try again.');
        }
    }

    /**
     * Get real-time user data.
     */
    public function getRealtimeData(Request $request)
    {
        try {
            $userId = auth()->id();
            
            // Get comprehensive analytics data for user
            $analyticsService = app(\App\Services\FinancialAnalyticsService::class);
            $analytics = $analyticsService->getComprehensiveAnalytics(null, $userId);
            
            // Get user dashboard data
            $data = $this->realtimeService->getUserData($userId);
            
            return response()->json([
                'success' => true,
                'data' => array_merge($data, $analytics),
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching user data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export personal report.
     */
    public function exportReport(Request $request)
    {
        try {
            $userId = auth()->id();
            $data = $this->realtimeService->getUserData($userId);
            
            // Implement personal report export logic here
            return response()->json([
                'success' => true,
                'message' => 'Personal report export functionality will be implemented'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting personal report: ' . $e->getMessage()
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
