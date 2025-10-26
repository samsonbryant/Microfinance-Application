<?php

namespace App\Http\Controllers;

use App\Services\RealtimeDashboardService;
use App\Services\FinancialReportsService;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    protected $realtimeDashboardService;
    protected $financialReportsService;
    protected $accountingService;

    public function __construct(
        RealtimeDashboardService $realtimeDashboardService,
        FinancialReportsService $financialReportsService,
        AccountingService $accountingService
    ) {
        $this->realtimeDashboardService = $realtimeDashboardService;
        $this->financialReportsService = $financialReportsService;
        $this->accountingService = $accountingService;
        
        $this->middleware('auth');
    }

    /**
     * Main dashboard - redirect to role-specific dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Redirect to role-specific dashboard
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('branch_manager')) {
            return redirect()->route('branch-manager.dashboard');
        } elseif ($user->hasRole('loan_officer')) {
            return redirect()->route('loan-officer.dashboard');
        } elseif ($user->hasRole('borrower')) {
            return redirect()->route('borrower.dashboard');
        }
        
        // Fallback to general dashboard
        $branchId = $user->branch_id;
        $userId = $user->id;

        // Get comprehensive dashboard data
        $dashboardData = $this->realtimeDashboardService->getDashboardData($userId, $branchId);

        // Get user permissions for navigation
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        return view('dashboard', compact('dashboardData', 'permissions'));
    }

    /**
     * Accounting dashboard
     */
    public function accounting()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        $userId = $user->id;

        // Get accounting-specific dashboard data
        $dashboardData = $this->realtimeDashboardService->getDashboardData($userId, $branchId);
        
        // Get additional accounting metrics
        $accountingMetrics = [
            'trial_balance' => $this->financialReportsService->getTrialBalance(),
            'profit_loss' => $this->financialReportsService->getProfitAndLoss(
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString()
            ),
            'balance_sheet' => $this->financialReportsService->getBalanceSheet(),
        ];

        return view('accounting.dashboard', compact('dashboardData', 'accountingMetrics'));
    }

    /**
     * Get real-time updates via AJAX
     */
    public function getRealtimeUpdates(Request $request)
    {
        $lastUpdate = $request->get('last_update');
        
        if ($lastUpdate) {
            $lastUpdate = \Carbon\Carbon::parse($lastUpdate);
        }

        $updates = $this->realtimeDashboardService->getRealtimeUpdates($lastUpdate);

        return response()->json([
            'success' => true,
            'data' => $updates,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get dashboard data via AJAX
     */
    public function getDashboardData(Request $request)
    {
        try {
            $user = Auth::user();
            $branchId = $request->get('branch_id', $user->branch_id);
            $userId = $request->get('user_id', $user->id);

            // Get comprehensive analytics data
            $analyticsService = app(\App\Services\FinancialAnalyticsService::class);
            $analytics = $analyticsService->getComprehensiveAnalytics($branchId, $userId);
            
            // Get dashboard data
            $dashboardData = $this->realtimeDashboardService->getDashboardData($userId, $branchId);

            return response()->json([
                'success' => true,
                'data' => array_merge($dashboardData, $analytics),
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear dashboard cache
     */
    public function clearCache()
    {
        $user = Auth::user();
        $this->realtimeDashboardService->clearDashboardCache($user->id, $user->branch_id);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard cache cleared successfully',
        ]);
    }

    /**
     * Get financial summary
     */
    public function getFinancialSummary(Request $request)
    {
        $branchId = $request->get('branch_id', Auth::user()->branch_id);
        
        $summary = $this->realtimeDashboardService->getDashboardData(null, $branchId)['financial_summary'];

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities(Request $request)
    {
        $userId = $request->get('user_id', Auth::user()->id);
        $branchId = $request->get('branch_id', Auth::user()->branch_id);
        $limit = $request->get('limit', 10);

        $activities = $this->realtimeDashboardService->getDashboardData($userId, $branchId)['recent_activities'];

        return response()->json([
            'success' => true,
            'data' => $activities->take($limit),
        ]);
    }

    /**
     * Get pending approvals
     */
    public function getPendingApprovals(Request $request)
    {
        $userId = $request->get('user_id', Auth::user()->id);
        $branchId = $request->get('branch_id', Auth::user()->branch_id);

        $approvals = $this->realtimeDashboardService->getDashboardData($userId, $branchId)['pending_approvals'];

        return response()->json([
            'success' => true,
            'data' => $approvals,
        ]);
    }

    /**
     * Get system alerts
     */
    public function getSystemAlerts(Request $request)
    {
        $branchId = $request->get('branch_id', Auth::user()->branch_id);

        $alerts = $this->realtimeDashboardService->getDashboardData(null, $branchId)['system_alerts'];

        return response()->json([
            'success' => true,
            'data' => $alerts,
        ]);
    }

    /**
     * Get chart data
     */
    public function getChartData(Request $request)
    {
        $branchId = $request->get('branch_id', Auth::user()->branch_id);
        $chartType = $request->get('chart_type', 'daily_transactions');

        $dashboardData = $this->realtimeDashboardService->getDashboardData(null, $branchId);
        $chartData = $dashboardData['chart_data'];

        $data = [];
        switch ($chartType) {
            case 'daily_transactions':
                $data = $chartData['daily_transactions'];
                break;
            case 'monthly_revenue_expense':
                $data = $chartData['monthly_revenue_expense'];
                break;
            case 'user_activity':
                $data = $dashboardData['user_activity'];
                break;
            case 'performance_metrics':
                $data = $dashboardData['performance_metrics'];
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get branch-specific data
     */
    public function getBranchData(Request $request, $branchId)
    {
        $dashboardData = $this->realtimeDashboardService->getDashboardData(null, $branchId);

        return response()->json([
            'success' => true,
            'data' => $dashboardData,
        ]);
    }

    /**
     * Get user-specific data
     */
    public function getUserData(Request $request, $userId)
    {
        $dashboardData = $this->realtimeDashboardService->getDashboardData($userId, null);

        return response()->json([
            'success' => true,
            'data' => $dashboardData,
        ]);
    }

    /**
     * Export dashboard data
     */
    public function exportDashboardData(Request $request)
    {
        $user = Auth::user();
        $branchId = $request->get('branch_id', $user->branch_id);
        $userId = $request->get('user_id', $user->id);
        $format = $request->get('format', 'json');

        $dashboardData = $this->realtimeDashboardService->getDashboardData($userId, $branchId);

        if ($format === 'json') {
            return response()->json($dashboardData);
        } elseif ($format === 'csv') {
            // Convert to CSV format
            $csvData = $this->convertToCsv($dashboardData);
            
            return response()->streamDownload(function () use ($csvData) {
                echo $csvData;
            }, 'dashboard-data-' . now()->format('Y-m-d') . '.csv', [
                'Content-Type' => 'text/csv',
            ]);
        }

        return response()->json(['error' => 'Invalid format'], 400);
    }

    /**
     * Convert dashboard data to CSV
     */
    private function convertToCsv($data)
    {
        $csv = "Metric,Value\n";
        
        // Financial Summary
        $csv .= "Today Debits,{$data['financial_summary']['today']['debits']}\n";
        $csv .= "Today Credits,{$data['financial_summary']['today']['credits']}\n";
        $csv .= "Today Net,{$data['financial_summary']['today']['net']}\n";
        $csv .= "This Month Debits,{$data['financial_summary']['this_month']['debits']}\n";
        $csv .= "This Month Credits,{$data['financial_summary']['this_month']['credits']}\n";
        $csv .= "This Month Net,{$data['financial_summary']['this_month']['net']}\n";
        
        // Pending Approvals
        $csv .= "Pending Journal Entries,{$data['pending_approvals']['journal_entries']}\n";
        $csv .= "Pending Expense Entries,{$data['pending_approvals']['expense_entries']}\n";
        $csv .= "Pending Reconciliations,{$data['pending_approvals']['reconciliations']}\n";
        
        // Loan Portfolio
        $csv .= "Total Loans,{$data['loan_portfolio_summary']['total_loans']}\n";
        $csv .= "Total Outstanding,{$data['loan_portfolio_summary']['total_outstanding']}\n";
        $csv .= "Active Loans,{$data['loan_portfolio_summary']['active_loans']}\n";
        $csv .= "Overdue Loans,{$data['loan_portfolio_summary']['overdue_loans']}\n";
        
        return $csv;
    }
}