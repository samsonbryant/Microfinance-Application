<?php

namespace App\Services;

use App\Models\GeneralLedgerEntry;
use App\Models\JournalEntry;
use App\Models\ExpenseEntry;
use App\Models\Reconciliation;
use App\Models\Loan;
use App\Models\SavingsAccount;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RealtimeDashboardService
{
    protected $accountingService;
    protected $financialReportsService;
    protected $reconciliationService;

    public function __construct(
        AccountingService $accountingService,
        FinancialReportsService $financialReportsService,
        ReconciliationService $reconciliationService
    ) {
        $this->accountingService = $accountingService;
        $this->financialReportsService = $financialReportsService;
        $this->reconciliationService = $reconciliationService;
    }

    /**
     * Get comprehensive dashboard data
     */
    public function getDashboardData($userId = null, $branchId = null)
    {
        $cacheKey = "dashboard_data_{$userId}_{$branchId}_" . now()->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 300, function () use ($userId, $branchId) {
            return [
                'financial_summary' => $this->getFinancialSummary($branchId),
                'recent_activities' => $this->getRecentActivities($userId, $branchId),
                'pending_approvals' => $this->getPendingApprovals($userId, $branchId),
                'reconciliation_status' => $this->getReconciliationStatus($branchId),
                'loan_portfolio_summary' => $this->getLoanPortfolioSummary($branchId),
                'savings_summary' => $this->getSavingsSummary($branchId),
                'user_activity' => $this->getUserActivity($userId, $branchId),
                'system_alerts' => $this->getSystemAlerts($branchId),
                'performance_metrics' => $this->getPerformanceMetrics($branchId),
                'chart_data' => $this->getChartData($branchId),
            ];
        });
    }

    /**
     * Get financial summary
     */
    private function getFinancialSummary($branchId = null)
    {
        $query = GeneralLedgerEntry::where('status', 'approved');
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $today = now()->toDateString();
        $thisMonth = now()->startOfMonth()->toDateString();
        $lastMonth = now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthEnd = now()->subMonth()->endOfMonth()->toDateString();

        // Today's transactions
        $todayTransactions = $query->where('transaction_date', $today)->get();
        $todayDebits = $todayTransactions->sum('debit');
        $todayCredits = $todayTransactions->sum('credit');

        // This month's transactions
        $monthTransactions = $query->where('transaction_date', '>=', $thisMonth)->get();
        $monthDebits = $monthTransactions->sum('debit');
        $monthCredits = $monthTransactions->sum('credit');

        // Last month's transactions
        $lastMonthTransactions = $query->whereBetween('transaction_date', [$lastMonth, $lastMonthEnd])->get();
        $lastMonthDebits = $lastMonthTransactions->sum('debit');
        $lastMonthCredits = $lastMonthTransactions->sum('credit');

        // Get current balances
        $cashAccount = \App\Models\ChartOfAccount::where('code', '1000')->first();
        $loanPortfolio = \App\Models\ChartOfAccount::where('code', '1200')->first();
        $clientSavings = \App\Models\ChartOfAccount::where('code', '2000')->first();

        return [
            'today' => [
                'debits' => $todayDebits,
                'credits' => $todayCredits,
                'net' => $todayCredits - $todayDebits,
                'transactions' => $todayTransactions->count(),
            ],
            'this_month' => [
                'debits' => $monthDebits,
                'credits' => $monthCredits,
                'net' => $monthCredits - $monthDebits,
                'transactions' => $monthTransactions->count(),
            ],
            'last_month' => [
                'debits' => $lastMonthDebits,
                'credits' => $lastMonthCredits,
                'net' => $lastMonthCredits - $lastMonthDebits,
                'transactions' => $lastMonthTransactions->count(),
            ],
            'current_balances' => [
                'cash_on_hand' => $cashAccount ? $cashAccount->getCurrentBalance() : 0,
                'loan_portfolio' => $loanPortfolio ? $loanPortfolio->getCurrentBalance() : 0,
                'client_savings' => $clientSavings ? $clientSavings->getCurrentBalance() : 0,
            ],
            'growth' => [
                'monthly_growth' => $this->calculateGrowth($monthCredits - $monthDebits, $lastMonthCredits - $lastMonthDebits),
            ],
        ];
    }

    /**
     * Get recent activities (loan applications)
     */
    private function getRecentActivities($userId = null, $branchId = null)
    {
        $query = Loan::with(['client', 'branch']);
        
        if ($userId) {
            $query->where('created_by', $userId);
        }
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get pending approvals
     */
    private function getPendingApprovals($userId = null, $branchId = null)
    {
        $approvals = [];

        // Pending journal entries
        $journalQuery = JournalEntry::where('status', 'pending');
        if ($branchId) {
            $journalQuery->where('branch_id', $branchId);
        }
        $approvals['journal_entries'] = $journalQuery->with(['createdBy', 'branch'])->get();

        // Pending expense entries
        $expenseQuery = ExpenseEntry::where('status', 'pending');
        if ($branchId) {
            $expenseQuery->where('branch_id', $branchId);
        }
        $approvals['expense_entries'] = $expenseQuery->with(['user', 'account', 'branch'])->get();

        // Pending reconciliations
        $reconciliationQuery = Reconciliation::where('status', 'completed');
        if ($branchId) {
            $reconciliationQuery->where('branch_id', $branchId);
        }
        $approvals['reconciliations'] = $reconciliationQuery->with(['user', 'account', 'branch'])->get();

        return [
            'journal_entries' => $approvals['journal_entries']->count(),
            'expense_entries' => $approvals['expense_entries']->count(),
            'reconciliations' => $approvals['reconciliations']->count(),
            'total' => $approvals['journal_entries']->count() + $approvals['expense_entries']->count() + $approvals['reconciliations']->count(),
            'details' => $approvals,
        ];
    }

    /**
     * Get reconciliation status
     */
    private function getReconciliationStatus($branchId = null)
    {
        $query = Reconciliation::query();
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $reconciliations = $query->get();

        return [
            'total' => $reconciliations->count(),
            'completed' => $reconciliations->where('status', 'completed')->count(),
            'approved' => $reconciliations->where('status', 'approved')->count(),
            'in_progress' => $reconciliations->where('status', 'in_progress')->count(),
            'draft' => $reconciliations->where('status', 'draft')->count(),
            'overdue' => $this->reconciliationService->getOverdueReconciliations()->count(),
            'total_variance' => $reconciliations->sum('variance'),
        ];
    }

    /**
     * Get loan portfolio summary
     */
    private function getLoanPortfolioSummary($branchId = null)
    {
        $query = Loan::query();
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $loans = $query->get();

        return [
            'total_loans' => $loans->count(),
            'total_outstanding' => $loans->sum('outstanding_balance'),
            'active_loans' => $loans->where('status', 'active')->count(),
            'overdue_loans' => $loans->where('status', 'overdue')->count(),
            'disbursed_today' => $loans->where('disbursement_date', now()->toDateString())->count(),
            'disbursed_this_month' => $loans->where('disbursement_date', '>=', now()->startOfMonth())->count(),
        ];
    }

    /**
     * Get savings summary
     */
    private function getSavingsSummary($branchId = null)
    {
        $query = SavingsAccount::query();
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $savings = $query->get();

        return [
            'total_accounts' => $savings->count(),
            'total_balance' => $savings->sum('balance'),
            'active_accounts' => $savings->where('status', 'active')->count(),
            'deposits_today' => $savings->where('last_deposit_date', now()->toDateString())->count(),
            'deposits_this_month' => $savings->where('last_deposit_date', '>=', now()->startOfMonth())->count(),
        ];
    }

    /**
     * Get user activity
     */
    private function getUserActivity($userId = null, $branchId = null)
    {
        $query = \Spatie\Activitylog\Models\Activity::with('causer');
        
        if ($userId) {
            $query->where('causer_id', $userId);
        }

        if ($branchId) {
            $query->whereHas('subject', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $activities = $query->where('created_at', '>=', now()->subDays(7))->get();

        return [
            'total_activities' => $activities->count(),
            'unique_users' => $activities->pluck('causer_id')->unique()->count(),
            'activities_by_day' => $activities->groupBy(function ($activity) {
                return $activity->created_at->format('Y-m-d');
            })->map->count(),
            'top_users' => $activities->groupBy('causer_id')->map->count()->sortDesc()->take(5),
        ];
    }

    /**
     * Get system alerts
     */
    private function getSystemAlerts($branchId = null)
    {
        $alerts = [];

        // Overdue reconciliations
        $overdueReconciliations = $this->reconciliationService->getOverdueReconciliations();
        if ($overdueReconciliations->count() > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Overdue Reconciliations',
                'message' => "{$overdueReconciliations->count()} reconciliations are overdue",
                'count' => $overdueReconciliations->count(),
            ];
        }

        // Large variances
        $largeVariances = Reconciliation::where('status', '!=', 'approved')
            ->whereRaw('ABS(variance) > 1000')
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->count();

        if ($largeVariances > 0) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Large Variances',
                'message' => "{$largeVariances} reconciliations have large variances",
                'count' => $largeVariances,
            ];
        }

        // Unbalanced journal entries
        $unbalancedJournals = JournalEntry::where('status', '!=', 'approved')
            ->whereRaw('ABS(total_debits - total_credits) > 0.01')
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->count();

        if ($unbalancedJournals > 0) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Unbalanced Journal Entries',
                'message' => "{$unbalancedJournals} journal entries are not balanced",
                'count' => $unbalancedJournals,
            ];
        }

        return $alerts;
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics($branchId = null)
    {
        $today = now()->toDateString();
        $thisMonth = now()->startOfMonth()->toDateString();

        // Transaction volume
        $transactionQuery = GeneralLedgerEntry::where('status', 'approved');
        if ($branchId) {
            $transactionQuery->where('branch_id', $branchId);
        }

        $todayTransactions = $transactionQuery->where('transaction_date', $today)->count();
        $monthTransactions = $transactionQuery->where('transaction_date', '>=', $thisMonth)->count();

        // User activity
        $userQuery = \Spatie\Activitylog\Models\Activity::query();
        if ($branchId) {
            $userQuery->whereHas('subject', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $todayActivities = $userQuery->whereDate('created_at', $today)->count();
        $monthActivities = $userQuery->where('created_at', '>=', $thisMonth)->count();

        return [
            'transactions' => [
                'today' => $todayTransactions,
                'this_month' => $monthTransactions,
            ],
            'user_activities' => [
                'today' => $todayActivities,
                'this_month' => $monthActivities,
            ],
            'efficiency' => [
                'avg_transactions_per_day' => $monthTransactions / now()->day,
                'avg_activities_per_day' => $monthActivities / now()->day,
            ],
        ];
    }

    /**
     * Get chart data
     */
    private function getChartData($branchId = null)
    {
        $query = GeneralLedgerEntry::where('status', 'approved');
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Last 30 days transaction data
        $last30Days = $query->where('transaction_date', '>=', now()->subDays(30))
            ->selectRaw('DATE(transaction_date) as date, SUM(debit) as total_debits, SUM(credit) as total_credits')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Monthly revenue and expense data
        $monthlyData = $query->join('chart_of_accounts', 'general_ledger_entries.account_id', '=', 'chart_of_accounts.id')
            ->whereIn('chart_of_accounts.type', ['revenue', 'expense'])
            ->selectRaw('chart_of_accounts.type, strftime("%m", transaction_date) as month, SUM(general_ledger_entries.credit) as revenue, SUM(general_ledger_entries.debit) as expense')
            ->groupBy('chart_of_accounts.type', 'month')
            ->get();

        return [
            'daily_transactions' => $last30Days,
            'monthly_revenue_expense' => $monthlyData,
        ];
    }

    /**
     * Calculate growth percentage
     */
    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return (($current - $previous) / $previous) * 100;
    }

    /**
     * Clear dashboard cache
     */
    public function clearDashboardCache($userId = null, $branchId = null)
    {
        $cacheKey = "dashboard_data_{$userId}_{$branchId}_" . now()->format('Y-m-d-H');
        Cache::forget($cacheKey);
    }

    /**
     * Get real-time updates
     */
    public function getRealtimeUpdates($lastUpdate = null)
    {
        $updates = [];

        // Get new activities since last update
        if ($lastUpdate) {
            $newActivities = \Spatie\Activitylog\Models\Activity::where('created_at', '>', $lastUpdate)
                ->with(['causer', 'subject'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            $updates['activities'] = $newActivities;
        }

        // Get pending approvals count
        $updates['pending_approvals'] = $this->getPendingApprovals();

        // Get system alerts
        $updates['alerts'] = $this->getSystemAlerts();

        return $updates;
    }

    /**
     * Get branch-specific dashboard data
     */
    public function getBranchData($branchId)
    {
        $cacheKey = "branch_data_{$branchId}_" . now()->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 300, function () use ($branchId) {
            return [
                'branch_summary' => $this->getBranchSummary($branchId),
                'branch_loans' => $this->getBranchLoans($branchId),
                'branch_savings' => $this->getBranchSavings($branchId),
                'branch_clients' => $this->getBranchClients($branchId),
                'branch_performance' => $this->getBranchPerformance($branchId),
                'branch_charts' => $this->getBranchCharts($branchId),
                'timestamp' => now()->toISOString()
            ];
        });
    }

    /**
     * Get user-specific dashboard data
     */
    public function getUserData($userId)
    {
        $cacheKey = "user_data_{$userId}_" . now()->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 300, function () use ($userId) {
            return [
                'user_summary' => $this->getUserSummary($userId),
                'user_loans' => $this->getUserLoans($userId),
                'user_clients' => $this->getUserClients($userId),
                'user_performance' => $this->getUserPerformance($userId),
                'user_charts' => $this->getUserCharts($userId),
                'timestamp' => now()->toISOString()
            ];
        });
    }

    /**
     * Get branch summary
     */
    private function getBranchSummary($branchId)
    {
        return [
            'total_loans' => Loan::where('branch_id', $branchId)->count(),
            'total_savings' => SavingsAccount::where('branch_id', $branchId)->count(),
            'total_clients' => \App\Models\Client::where('branch_id', $branchId)->count(),
            'overdue_loans' => Loan::where('branch_id', $branchId)->where('status', 'overdue')->count(),
            'loan_portfolio' => Loan::where('branch_id', $branchId)->sum('amount'),
            'outstanding_balance' => Loan::where('branch_id', $branchId)->sum('outstanding_balance'),
            'savings_balance' => SavingsAccount::where('branch_id', $branchId)->sum('balance'),
            'overdue_amount' => Loan::where('branch_id', $branchId)->where('status', 'overdue')->sum('outstanding_balance')
        ];
    }

    /**
     * Get branch loans
     */
    private function getBranchLoans($branchId)
    {
        return Loan::where('branch_id', $branchId)
            ->with('client')
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Get branch savings
     */
    private function getBranchSavings($branchId)
    {
        return SavingsAccount::where('branch_id', $branchId)
            ->with('client')
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Get branch clients
     */
    private function getBranchClients($branchId)
    {
        return \App\Models\Client::where('branch_id', $branchId)
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Get branch performance
     */
    private function getBranchPerformance($branchId)
    {
        $totalLoans = Loan::where('branch_id', $branchId)->sum('amount');
        $outstanding = Loan::where('branch_id', $branchId)->sum('outstanding_balance');
        $collected = $totalLoans - $outstanding;
        $collectionRate = $totalLoans > 0 ? ($collected / $totalLoans) * 100 : 0;

        return [
            'collection_rate' => $collectionRate,
            'monthly_loans' => Loan::where('branch_id', $branchId)->whereMonth('created_at', now()->month)->count(),
            'monthly_savings' => SavingsAccount::where('branch_id', $branchId)->whereMonth('created_at', now()->month)->count()
        ];
    }

    /**
     * Get branch charts data
     */
    private function getBranchCharts($branchId)
    {
        return [
            'loan_status' => [
                'active' => Loan::where('branch_id', $branchId)->where('status', 'active')->count(),
                'overdue' => Loan::where('branch_id', $branchId)->where('status', 'overdue')->count(),
                'completed' => Loan::where('branch_id', $branchId)->where('status', 'completed')->count(),
                'pending' => Loan::where('branch_id', $branchId)->where('status', 'pending')->count()
            ],
            'monthly_performance' => $this->getMonthlyPerformance($branchId)
        ];
    }

    /**
     * Get user summary
     */
    private function getUserSummary($userId)
    {
        return [
            'my_loans' => Loan::where('created_by', $userId)->count(),
            'my_clients' => \App\Models\Client::where('created_by', $userId)->count(),
            'my_portfolio' => Loan::where('created_by', $userId)->sum('amount'),
            'my_overdue' => Loan::where('created_by', $userId)->where('status', 'overdue')->count(),
            'outstanding_balance' => Loan::where('created_by', $userId)->sum('outstanding_balance'),
            'overdue_amount' => Loan::where('created_by', $userId)->where('status', 'overdue')->sum('outstanding_balance')
        ];
    }

    /**
     * Get user loans
     */
    private function getUserLoans($userId)
    {
        return Loan::where('created_by', $userId)
            ->with('client')
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Get user clients
     */
    private function getUserClients($userId)
    {
        return \App\Models\Client::where('created_by', $userId)
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Get user performance
     */
    private function getUserPerformance($userId)
    {
        $totalLoans = Loan::where('created_by', $userId)->sum('amount');
        $outstanding = Loan::where('created_by', $userId)->sum('outstanding_balance');
        $collected = $totalLoans - $outstanding;
        $collectionRate = $totalLoans > 0 ? ($collected / $totalLoans) * 100 : 0;

        return [
            'collection_rate' => $collectionRate,
            'monthly_loans' => Loan::where('created_by', $userId)->whereMonth('created_at', now()->month)->count(),
            'monthly_clients' => \App\Models\Client::where('created_by', $userId)->whereMonth('created_at', now()->month)->count()
        ];
    }

    /**
     * Get user charts data
     */
    private function getUserCharts($userId)
    {
        return [
            'loan_status' => [
                'active' => Loan::where('created_by', $userId)->where('status', 'active')->count(),
                'overdue' => Loan::where('created_by', $userId)->where('status', 'overdue')->count(),
                'completed' => Loan::where('created_by', $userId)->where('status', 'completed')->count(),
                'pending' => Loan::where('created_by', $userId)->where('status', 'pending')->count()
            ],
            'monthly_performance' => $this->getUserMonthlyPerformance($userId)
        ];
    }

    /**
     * Get monthly performance for branch
     */
    private function getMonthlyPerformance($branchId)
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M'),
                'loans' => Loan::where('branch_id', $branchId)->whereMonth('created_at', $date->month)->count(),
                'savings' => SavingsAccount::where('branch_id', $branchId)->whereMonth('created_at', $date->month)->count()
            ];
        }
        return $months;
    }

    /**
     * Get monthly performance for user
     */
    private function getUserMonthlyPerformance($userId)
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M'),
                'loans' => Loan::where('created_by', $userId)->whereMonth('created_at', $date->month)->count(),
                'clients' => \App\Models\Client::where('created_by', $userId)->whereMonth('created_at', $date->month)->count()
            ];
        }
        return $months;
    }
}
