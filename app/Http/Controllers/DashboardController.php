<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Loan;
use App\Models\LoanApplication;
use App\Models\LoanRepayment;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use App\Models\AuditLog;
use App\Services\DashboardService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $role = $user->getRoleNames()->first() ?? 'admin';
        $branchId = $user->branch_id;

        // Use DashboardService for role-specific metrics
        $dashboardService = app(DashboardService::class);
        $metrics = $dashboardService->getMetrics($role, $branchId);
        
        // Get role-specific data
        if ($role === 'loan_officer') {
            $recentActivities = $this->getRecentActivities($branchId);
            $monthlyTrends = $this->getMonthlyTrends($branchId);
            $overdueLoans = $this->getOverdueLoans($branchId);
            $pendingApplications = $this->getPendingApplications($branchId);
        } elseif ($role === 'hr') {
            $recentActivities = $this->getRecentActivities($branchId);
            $monthlyTrends = $this->getMonthlyTrends($branchId);
            $staff = $this->getStaffData();
            $overdueLoans = collect();
            $pendingApplications = collect();
        } elseif ($role === 'borrower') {
            $recentActivities = $this->getRecentActivities($branchId);
            $monthlyTrends = $this->getMonthlyTrends($branchId);
            $myLoans = $this->getMyLoans($user->client_id);
            $mySavings = $this->getMySavings($user->client_id);
            $recentPayments = $this->getRecentPayments($user->client_id);
            $myApplications = $this->getMyApplications($user->client_id);
            $overdueLoans = collect();
            $pendingApplications = collect();
        } else {
            $recentActivities = $this->getRecentActivities($branchId);
            $monthlyTrends = $this->getMonthlyTrends($branchId);
            $overdueLoans = $this->getOverdueLoans($branchId);
            $pendingApplications = $this->getPendingApplications($branchId);
        }
        
        // Get portfolio at risk data
        $portfolioAtRisk = $this->getPortfolioAtRisk($branchId);
        
        // Get branch performance data
        $branchPerformance = $this->getBranchPerformance($branchId);
        
        // Get loan stats
        $loanStats = $this->getLoanStats($branchId);
        
        // Get dashboard stats
        $stats = $this->getDashboardStats($branchId, $role);

        // Role-specific data
        if ($role === 'admin') {
            $stats = array_merge($stats, [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_branches' => Branch::count(),
                'active_branches' => Branch::where('is_active', true)->count(),
                'total_loan_portfolio' => Loan::where('status', 'active')->sum('principal_amount'),
                'active_loans' => Loan::where('status', 'active')->sum('principal_amount'),
                'total_savings' => SavingsAccount::sum('balance'),
                'savings_accounts' => SavingsAccount::count(),
                'overdue_loans' => Loan::where('status', 'overdue')->count(),
                'overdue_amount' => Loan::where('status', 'overdue')->sum('principal_amount'),
                'par_percentage' => $this->calculateParPercentage(),
                'monthly_revenue' => $this->getMonthlyRevenue(),
                'revenue_growth' => $this->getRevenueGrowth(),
                'net_profit' => $this->getNetProfit(),
                'profit_margin' => $this->getProfitMargin(),
                'pending_jobs' => DB::table('jobs')->count(),
                'storage_usage' => $this->getStorageUsage(),
                'low_risk_loans' => $this->getRiskDistribution('low'),
                'medium_risk_loans' => $this->getRiskDistribution('medium'),
                'high_risk_loans' => $this->getRiskDistribution('high'),
                'defaulted_loans' => $this->getRiskDistribution('defaulted'),
            ]);

            // Get recent audit logs for admin
            $recentActivities['audit_logs'] = AuditLog::with('causer')
                ->latest()
                ->limit(10)
                ->get();
        } elseif ($role === 'general_manager') {
            $stats = array_merge($stats, [
                'total_clients' => Client::count(),
                'active_clients' => Client::where('status', 'active')->count(),
                'total_loan_portfolio' => Loan::where('status', 'active')->sum('principal_amount'),
                'active_loans' => Loan::where('status', 'active')->sum('principal_amount'),
                'par_percentage' => $this->calculateParPercentage(),
                'monthly_revenue' => $this->getMonthlyRevenue(),
                'revenue_growth' => $this->getRevenueGrowth(),
                'low_risk_loans' => $this->getRiskDistribution('low'),
                'medium_risk_loans' => $this->getRiskDistribution('medium'),
                'high_risk_loans' => $this->getRiskDistribution('high'),
                'defaulted_loans' => $this->getRiskDistribution('defaulted'),
            ]);
        } elseif ($role === 'loan_officer') {
            $stats = array_merge($stats, [
                'pending_applications' => LoanApplication::where('status', 'pending')->count(),
                'active_loans' => Loan::where('status', 'active')->count(),
                'active_loan_value' => Loan::where('status', 'active')->sum('principal_amount'),
                'overdue_loans' => Loan::where('status', 'overdue')->count(),
                'overdue_amount' => Loan::where('status', 'overdue')->sum('principal_amount'),
                'my_clients' => Client::where('assigned_officer_id', $user->id)->count(),
            ]);
        } elseif ($role === 'hr') {
            $stats = array_merge($stats, [
                'total_staff' => User::whereHas('roles', function($q) {
                    $q->whereIn('name', ['loan_officer', 'hr', 'general_manager']);
                })->count(),
                'active_staff' => User::where('is_active', true)->whereHas('roles', function($q) {
                    $q->whereIn('name', ['loan_officer', 'hr', 'general_manager']);
                })->count(),
                'present_today' => $this->getPresentTodayCount(),
                'attendance_rate' => $this->getAttendanceRate(),
                'pending_leaves' => $this->getPendingLeavesCount(),
                'monthly_payroll' => $this->getMonthlyPayroll(),
                'pending_approvals' => $this->getPendingApprovalsCount(),
                'pending_hires' => $this->getPendingHiresCount(),
                'pending_payroll' => $this->getPendingPayrollCount(),
                'excellent_performers' => $this->getPerformanceCount('excellent'),
                'good_performers' => $this->getPerformanceCount('good'),
                'average_performers' => $this->getPerformanceCount('average'),
                'poor_performers' => $this->getPerformanceCount('poor'),
            ]);
        } elseif ($role === 'borrower') {
            $stats = array_merge($stats, [
                'active_loans' => Loan::where('client_id', $user->client_id)->where('status', 'active')->count(),
                'total_loan_amount' => Loan::where('client_id', $user->client_id)->where('status', 'active')->sum('principal_amount'),
                'savings_balance' => SavingsAccount::where('client_id', $user->client_id)->sum('balance'),
                'savings_accounts' => SavingsAccount::where('client_id', $user->client_id)->count(),
                'upcoming_payments' => $this->getUpcomingPaymentsCount($user->client_id),
                'credit_score' => $this->getCreditScore($user->client_id),
                'last_credit_update' => $this->getLastCreditUpdate($user->client_id),
            ]);
        }

        // Determine which dashboard view to show based on role
        $dashboardView = 'dashboard.' . $role;
        
        // If role-specific view doesn't exist, fall back to general dashboard
        if (!view()->exists($dashboardView)) {
            $dashboardView = 'dashboard.index';
        }

        // Prepare variables for view
        $viewData = compact(
            'metrics',
            'recentActivities',
            'monthlyTrends',
            'overdueLoans',
            'pendingApplications',
            'portfolioAtRisk',
            'branchPerformance',
            'loanStats',
            'stats'
        );

        // Add role-specific variables
        if ($role === 'hr') {
            $viewData['staff'] = $staff ?? collect();
        } elseif ($role === 'borrower') {
            $viewData['myLoans'] = $myLoans ?? collect();
            $viewData['mySavings'] = $mySavings ?? collect();
            $viewData['recentPayments'] = $recentPayments ?? collect();
            $viewData['myApplications'] = $myApplications ?? collect();
        }

        return view($dashboardView, $viewData);
    }

    private function getDashboardStats($branchId, $userRole)
    {
        $query = $this->getBranchQuery($branchId, $userRole);

        return [
            'total_users' => User::when($branchId, fn($q) => $q->where('branch_id', $branchId))->count(),
            'total_branches' => Branch::active()->count(),
            'total_clients' => $query(Client::class)->count(),
            'total_loans' => $query(Loan::class)->count(),
            'total_savings_accounts' => $query(SavingsAccount::class)->count(),
            'total_transactions' => $query(Transaction::class)->count(),
            'total_loan_applications' => $query(LoanApplication::class)->count(),
        ];
    }

    private function getLoanStats($branchId)
    {
        $query = $this->getBranchQuery($branchId);

        return [
            'active_loans' => $query(Loan::class)->where('status', 'active')->count(),
            'overdue_loans' => $query(Loan::class)->where('status', 'overdue')->count(),
            'pending_applications' => $query(LoanApplication::class)->pending()->count(),
            'total_loan_amount' => $query(Loan::class)->whereIn('status', ['active', 'overdue', 'disbursed'])->sum('amount'),
            'total_outstanding' => $query(Loan::class)->whereIn('status', ['active', 'overdue', 'disbursed'])->sum('outstanding_balance'),
            'total_disbursed_this_month' => $query(Loan::class)
                ->where('status', 'disbursed')
                ->whereMonth('disbursement_date', now()->month)
                ->whereYear('disbursement_date', now()->year)
                ->sum('amount'),
        ];
    }

    private function getSavingsStats($branchId)
    {
        $query = $this->getBranchQuery($branchId);

        return [
            'active_accounts' => $query(SavingsAccount::class)->where('status', 'active')->count(),
            'total_balance' => $query(SavingsAccount::class)->where('status', 'active')->sum('balance'),
            'total_deposits_this_month' => $query(Transaction::class)
                ->where('type', 'deposit')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'total_withdrawals_this_month' => $query(Transaction::class)
                ->where('type', 'withdrawal')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
        ];
    }

    private function getPortfolioAtRisk($branchId)
    {
        $query = $this->getBranchQuery($branchId);

        $overdueLoans = $query(Loan::class)->where('status', 'overdue');
        $overdueAmount = $overdueLoans->sum('outstanding_balance');
        $totalPortfolio = $query(Loan::class)->whereIn('status', ['active', 'disbursed', 'overdue'])->sum('outstanding_balance');

        $parPercentage = $totalPortfolio > 0 ? ($overdueAmount / $totalPortfolio) * 100 : 0;

        return [
            'overdue_amount' => $overdueAmount,
            'total_portfolio' => $totalPortfolio,
            'par_percentage' => round($parPercentage, 2),
            'overdue_count' => $overdueLoans->count(),
        ];
    }

    private function getRecentActivities($branchId)
    {
        $query = $this->getBranchQuery($branchId);

        return [
            'recent_transactions' => $query(Transaction::class)
                ->with(['client', 'loan'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'recent_clients' => $query(Client::class)
                ->with('branch')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'recent_applications' => $query(LoanApplication::class)
                ->with(['client', 'loanOfficer'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    private function getMonthlyTrends($branchId)
    {
        $query = $this->getBranchQuery($branchId);

        // Get last 6 months of data
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->push([
                'month' => $date->format('M Y'),
                'loans_disbursed' => $query(Loan::class)
                    ->whereMonth('disbursement_date', $date->month)
                    ->whereYear('disbursement_date', $date->year)
                    ->sum('amount'),
                'loans_collected' => $query(LoanRepayment::class)
                    ->whereMonth('actual_payment_date', $date->month)
                    ->whereYear('actual_payment_date', $date->year)
                    ->sum('total_paid'),
                'new_clients' => $query(Client::class)
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
            ]);
        }

        return $months;
    }

    private function getBranchPerformance($branchId)
    {
        if (!$branchId) {
            // Head office view - show all branches
            return Branch::withCount(['clients', 'loans'])
                ->withSum('loans', 'outstanding_balance')
                ->get()
                ->map(function ($branch) {
                    return (object) [
                        'name' => $branch->name,
                        'clients_count' => $branch->clients_count,
                        'loans_count' => $branch->loans_count,
                        'active_loans_count' => $branch->loans_count, // Use loans_count as active_loans for admin view
                        'loan_portfolio' => $branch->loans_sum_outstanding_balance ?? 0,
                        'savings_balance' => 0, // Add savings_balance for consistency
                        'par_percentage' => 0, // Add par_percentage for consistency
                        'is_active' => $branch->is_active ?? true, // Add is_active property
                    ];
                });
        }

        // Single branch view - wrap in collection for consistency
        $branch = Branch::find($branchId);
        if (!$branch) {
            return collect();
        }
        
        return collect([
            (object) [
                'name' => $branch->name,
                'clients_count' => $branch->getTotalClients(),
                'loans_count' => $branch->getTotalLoans(),
                'active_loans_count' => $branch->getActiveLoans(),
                'loan_portfolio' => $branch->getTotalLoanPortfolio(),
                'savings_balance' => $branch->getTotalSavings(),
                'par_percentage' => 0, // Add par_percentage for consistency
                'is_active' => $branch->is_active ?? true, // Add is_active property
            ]
        ]);
    }

    private function getOverdueLoans($branchId)
    {
        $query = $this->getBranchQuery($branchId);

        return $query(Loan::class)
            ->with(['client', 'branch'])
            ->where('status', 'overdue')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();
    }

    private function getPendingApplications($branchId)
    {
        $query = $this->getBranchQuery($branchId);

        return $query(LoanApplication::class)
            ->with(['client', 'loanOfficer'])
            ->pending()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    private function getBranchQuery($branchId, $userRole = null)
    {
        return function ($model) use ($branchId, $userRole) {
            $query = $model::query();
            
            // Branch-level filtering
            if ($branchId && $userRole !== 'admin') {
                $query->where('branch_id', $branchId);
            }
            
            return $query;
        };
    }

    public function analytics()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        // Advanced analytics data
        $analytics = [
            'loan_performance' => $this->getLoanPerformanceAnalytics($branchId),
            'client_demographics' => $this->getClientDemographics($branchId),
            'repayment_trends' => $this->getRepaymentTrends($branchId),
            'risk_analysis' => $this->getRiskAnalysis($branchId),
            'profitability' => $this->getProfitabilityAnalysis($branchId),
        ];

        return view('dashboard.analytics', compact('analytics'));
    }

    private function getLoanPerformanceAnalytics($branchId)
    {
        // Implementation for loan performance analytics
        return [
            'approval_rate' => 85.5,
            'default_rate' => 2.3,
            'average_loan_size' => 15000,
            'average_repayment_period' => 18,
        ];
    }

    private function getClientDemographics($branchId)
    {
        // Implementation for client demographics
        return [
            'age_groups' => ['18-25' => 15, '26-35' => 35, '36-45' => 30, '46-55' => 15, '55+' => 5],
            'gender_distribution' => ['male' => 60, 'female' => 40],
            'occupation_types' => ['business' => 45, 'employed' => 35, 'farmer' => 20],
        ];
    }

    private function getRepaymentTrends($branchId)
    {
        // Implementation for repayment trends
        return [
            'on_time_rate' => 92.5,
            'early_payment_rate' => 25.0,
            'average_days_overdue' => 5.2,
        ];
    }

    private function getRiskAnalysis($branchId)
    {
        // Implementation for risk analysis
        return [
            'high_risk_loans' => 12,
            'medium_risk_loans' => 45,
            'low_risk_loans' => 143,
            'risk_score_average' => 72.5,
        ];
    }

    private function getProfitabilityAnalysis($branchId)
    {
        // Implementation for profitability analysis
        return [
            'monthly_interest_income' => 45000,
            'monthly_operating_expenses' => 25000,
            'net_profit_margin' => 44.4,
            'roi_percentage' => 15.8,
        ];
    }

    // Admin-specific helper methods
    private function calculateParPercentage()
    {
        $overdueAmount = Loan::where('status', 'overdue')->sum('principal_amount');
        $totalPortfolio = Loan::whereIn('status', ['active', 'disbursed', 'overdue'])->sum('principal_amount');
        
        return $totalPortfolio > 0 ? round(($overdueAmount / $totalPortfolio) * 100, 2) : 0;
    }

    private function getMonthlyRevenue()
    {
        return Transaction::where('type', 'interest')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount') ?? 0;
    }

    private function getRevenueGrowth()
    {
        $currentMonth = Transaction::where('type', 'interest')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount') ?? 0;

        $lastMonth = Transaction::where('type', 'interest')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('amount') ?? 0;

        return $lastMonth > 0 ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 2) : 0;
    }

    private function getNetProfit()
    {
        $revenue = $this->getMonthlyRevenue();
        $expenses = Transaction::where('type', 'expense')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount') ?? 0;

        return $revenue - $expenses;
    }

    private function getProfitMargin()
    {
        $revenue = $this->getMonthlyRevenue();
        $profit = $this->getNetProfit();

        return $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;
    }

    private function getStorageUsage()
    {
        $totalSpace = disk_total_space(storage_path());
        $freeSpace = disk_free_space(storage_path());
        $usedSpace = $totalSpace - $freeSpace;
        
        return $totalSpace > 0 ? round(($usedSpace / $totalSpace) * 100, 2) : 0;
    }

    private function getRiskDistribution($riskLevel)
    {
        // This is a simplified implementation
        // In a real system, you'd have a risk assessment system
        switch ($riskLevel) {
            case 'low':
                return Loan::where('status', 'active')->where('principal_amount', '<', 10000)->count();
            case 'medium':
                return Loan::where('status', 'active')->whereBetween('principal_amount', [10000, 50000])->count();
            case 'high':
                return Loan::where('status', 'active')->where('principal_amount', '>', 50000)->count();
            case 'defaulted':
                return Loan::where('status', 'defaulted')->count();
            default:
                return 0;
        }
    }

    // HR-specific helper methods
    private function getPresentTodayCount()
    {
        // Simplified implementation - in real system, check attendance records
        return User::where('is_active', true)->count() - 2; // Mock data
    }

    private function getAttendanceRate()
    {
        // Simplified implementation
        return 92.5; // Mock data
    }

    private function getPendingLeavesCount()
    {
        // Simplified implementation
        return 3; // Mock data
    }

    private function getMonthlyPayroll()
    {
        // Simplified implementation
        return 50000; // Mock data
    }

    private function getPendingApprovalsCount()
    {
        return $this->getPendingLeavesCount() + $this->getPendingHiresCount() + $this->getPendingPayrollCount();
    }

    private function getPendingHiresCount()
    {
        // Simplified implementation
        return 2; // Mock data
    }

    private function getPendingPayrollCount()
    {
        // Simplified implementation
        return 1; // Mock data
    }

    private function getPerformanceCount($level)
    {
        // Simplified implementation
        $counts = [
            'excellent' => 5,
            'good' => 8,
            'average' => 3,
            'poor' => 1
        ];
        return $counts[$level] ?? 0;
    }

    private function getStaffData()
    {
        return User::whereHas('roles', function($q) {
            $q->whereIn('name', ['loan_officer', 'hr', 'general_manager']);
        })->with('roles')->get()->map(function($user) {
            return (object) [
                'name' => $user->name,
                'position' => $user->roles->first()->name ?? 'Staff',
                'department' => 'Operations',
                'is_active' => $user->is_active,
                'join_date' => $user->created_at,
                'performance_score' => rand(60, 95) // Mock data
            ];
        });
    }

    // Borrower-specific helper methods
    private function getMyLoans($clientId)
    {
        return Loan::where('client_id', $clientId)
            ->whereIn('status', ['active', 'overdue', 'disbursed'])
            ->get();
    }

    private function getMySavings($clientId)
    {
        return SavingsAccount::where('client_id', $clientId)->get();
    }

    private function getRecentPayments($clientId)
    {
        return Transaction::where('client_id', $clientId)
            ->whereIn('type', ['loan_payment', 'savings_deposit'])
            ->latest()
            ->limit(10)
            ->get();
    }

    private function getMyApplications($clientId)
    {
        return LoanApplication::where('client_id', $clientId)
            ->latest()
            ->limit(10)
            ->get();
    }

    private function getUpcomingPaymentsCount($clientId)
    {
        // Simplified implementation
        return 2; // Mock data
    }

    private function getCreditScore($clientId)
    {
        // Simplified implementation
        return rand(650, 850); // Mock data
    }

    private function getLastCreditUpdate($clientId)
    {
        // Simplified implementation
        return now()->subDays(rand(1, 30))->format('M d, Y'); // Mock data
    }
}
