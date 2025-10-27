<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Client;
use App\Models\Transaction;
use App\Models\SavingsAccount;
use App\Models\Branch;
use App\Models\User;
use App\Services\AccountingService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->middleware('auth');
        $this->accountingService = $accountingService;
    }

    /**
     * Display the main reports dashboard
     */
    public function index()
    {
        $user = auth()->user();
        $role = $user->getRoleNames()->first() ?? 'admin';
        
        // Get summary statistics
        $summary = $this->getSummaryStatistics();
        
        // Get recent reports
        $recentReports = $this->getRecentReports();
        
        // Get report types
        $reportTypes = [
            'portfolio_at_risk' => 'Portfolio at Risk',
            'loan_performance' => 'Loan Performance',
            'client_demographics' => 'Client Demographics',
            'financial_summary' => 'Financial Summary',
            'branch_performance' => 'Branch Performance',
            'collections_report' => 'Collections Report',
            'recovery_report' => 'Recovery Report',
            'audit_trail' => 'Audit Trail',
        ];
        
        return view('reports.index', compact('summary', 'recentReports', 'role', 'reportTypes'));
    }

    /**
     * Portfolio Report
     */
    public function portfolio(Request $request)
    {
        $filters = $this->getFilters($request);
        
        $loans = Loan::with(['client', 'branch'])
            ->when($filters['branch_id'], fn($q) => $q->where('branch_id', $filters['branch_id']))
            ->when($filters['status'], fn($q) => $q->where('status', $filters['status']))
            ->when($filters['date_from'], fn($q) => $q->where('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn($q) => $q->where('created_at', '<=', $filters['date_to']))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $portfolioStats = $this->getPortfolioStatistics($filters);
        
        return view('reports.portfolio', compact('loans', 'portfolioStats', 'filters'));
    }

    /**
     * Collections Report
     */
    public function collections(Request $request)
    {
        $filters = $this->getFilters($request);
        
        $collections = $this->getCollectionsData($filters);
        $collectionStats = $this->getCollectionStatistics($filters);
        
        return view('reports.collections', compact('collections', 'collectionStats', 'filters'));
    }

    /**
     * Performance Report
     */
    public function performance(Request $request)
    {
        $filters = $this->getFilters($request);
        
        $performance = $this->getPerformanceData($filters);
        $branchPerformance = $this->getBranchPerformanceData($filters);
        
        return view('reports.performance', compact('performance', 'branchPerformance', 'filters'));
    }

    /**
     * Financial Report
     */
    public function financial(Request $request)
    {
        $filters = $this->getFilters($request);
        
        // Convert Carbon instances to date strings
        $fromDate = isset($filters['date_from']) 
            ? (is_string($filters['date_from']) ? $filters['date_from'] : $filters['date_from']->toDateString())
            : now()->startOfMonth()->toDateString();
            
        $toDate = isset($filters['date_to'])
            ? (is_string($filters['date_to']) ? $filters['date_to'] : $filters['date_to']->toDateString())
            : now()->endOfMonth()->toDateString();
        
        $trialBalance = $this->accountingService->getTrialBalance($toDate);
        $profitLoss = $this->accountingService->getProfitAndLoss($fromDate, $toDate);
        $balanceSheet = $this->accountingService->getBalanceSheet($toDate);
        
        return view('reports.financial', compact('trialBalance', 'profitLoss', 'balanceSheet', 'filters'));
    }

    /**
     * Client Report
     */
    public function clients(Request $request)
    {
        $filters = $this->getFilters($request);
        
        $clients = Client::with(['branch', 'loans'])
            ->when($filters['branch_id'], fn($q) => $q->where('branch_id', $filters['branch_id']))
            ->when($filters['status'], fn($q) => $q->where('status', $filters['status']))
            ->when($filters['date_from'], fn($q) => $q->where('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn($q) => $q->where('created_at', '<=', $filters['date_to']))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $clientStats = $this->getClientStatistics($filters);
        
        return view('reports.clients', compact('clients', 'clientStats', 'filters'));
    }

    /**
     * Staff Report
     */
    public function staff(Request $request)
    {
        $filters = $this->getFilters($request);
        
        $staff = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'general_manager', 'branch_manager', 'loan_officer', 'hr']);
        })
        ->with(['roles', 'branch'])
        ->withCount(['loans', 'clients'])
        ->when($filters['branch_id'], fn($q) => $q->where('branch_id', $filters['branch_id']))
        ->when($filters['date_from'], fn($q) => $q->where('created_at', '>=', $filters['date_from']))
        ->when($filters['date_to'], fn($q) => $q->where('created_at', '<=', $filters['date_to']))
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        $staffStats = $this->getStaffStatistics($filters);
        
        return view('reports.staff', compact('staff', 'staffStats', 'filters'));
    }

    /**
     * Export report to PDF
     */
    public function exportPdf(Request $request, $type)
    {
        $filters = $this->getFilters($request);
        
        switch ($type) {
            case 'portfolio':
                return $this->exportPortfolioPdf($filters);
            case 'collections':
                return $this->exportCollectionsPdf($filters);
            case 'financial':
                return $this->exportFinancialPdf($filters);
            default:
                return redirect()->back()->with('error', 'Invalid report type');
        }
    }

    /**
     * Export report to Excel
     */
    public function exportExcel(Request $request, $type)
    {
        $filters = $this->getFilters($request);
        
        switch ($type) {
            case 'portfolio':
                return $this->exportPortfolioExcel($filters);
            case 'collections':
                return $this->exportCollectionsExcel($filters);
            case 'clients':
                return $this->exportClientsExcel($filters);
            default:
                return redirect()->back()->with('error', 'Invalid report type');
        }
    }

    /**
     * Get summary statistics
     */
    private function getSummaryStatistics()
    {
        return [
            'total_loans' => Loan::count(),
            'active_loans' => Loan::where('status', 'active')->count(),
            'total_portfolio' => Loan::where('status', 'active')->sum('principal_amount'),
            'overdue_loans' => Loan::where('status', 'overdue')->count(),
            'total_clients' => Client::count(),
            'total_savings' => SavingsAccount::sum('balance'),
            'monthly_disbursements' => Loan::whereMonth('created_at', now()->month)->sum('principal_amount'),
            'monthly_collections' => Transaction::where('type', 'loan_payment')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
        ];
    }

    /**
     * Get recent reports
     */
    private function getRecentReports()
    {
        return [
            ['name' => 'Portfolio Report', 'date' => now()->subDays(1), 'type' => 'portfolio'],
            ['name' => 'Collections Report', 'date' => now()->subDays(2), 'type' => 'collections'],
            ['name' => 'Financial Report', 'date' => now()->subDays(3), 'type' => 'financial'],
        ];
    }

    /**
     * Get filters from request
     */
    private function getFilters(Request $request)
    {
        return [
            'branch_id' => $request->get('branch_id'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from') ? Carbon::parse($request->get('date_from')) : null,
            'date_to' => $request->get('date_to') ? Carbon::parse($request->get('date_to')) : null,
        ];
    }

    /**
     * Get portfolio statistics
     */
    private function getPortfolioStatistics($filters)
    {
        $query = Loan::query()
            ->when($filters['branch_id'], fn($q) => $q->where('branch_id', $filters['branch_id']))
            ->when($filters['status'], fn($q) => $q->where('status', $filters['status']))
            ->when($filters['date_from'], fn($q) => $q->where('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn($q) => $q->where('created_at', '<=', $filters['date_to']));

        return [
            'total_loans' => $query->count(),
            'total_amount' => $query->sum('principal_amount'),
            'average_loan_size' => $query->avg('principal_amount'),
            'active_loans' => $query->where('status', 'active')->count(),
            'overdue_loans' => $query->where('status', 'overdue')->count(),
            'completed_loans' => $query->where('status', 'completed')->count(),
        ];
    }

    /**
     * Get collections data
     */
    private function getCollectionsData($filters)
    {
        return Transaction::where('type', 'loan_payment')
            ->with(['client', 'loan'])
            ->when($filters['branch_id'], fn($q) => $q->whereHas('client', fn($q) => $q->where('branch_id', $filters['branch_id'])))
            ->when($filters['date_from'], fn($q) => $q->where('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn($q) => $q->where('created_at', '<=', $filters['date_to']))
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    /**
     * Get collection statistics
     */
    private function getCollectionStatistics($filters)
    {
        $query = Transaction::where('type', 'loan_payment')
            ->when($filters['branch_id'], fn($q) => $q->whereHas('client', fn($q) => $q->where('branch_id', $filters['branch_id'])))
            ->when($filters['date_from'], fn($q) => $q->where('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn($q) => $q->where('created_at', '<=', $filters['date_to']));

        return [
            'total_collections' => $query->sum('amount'),
            'collection_count' => $query->count(),
            'average_collection' => $query->avg('amount'),
            'daily_collections' => $query->whereDate('created_at', now())->sum('amount'),
        ];
    }

    /**
     * Get performance data
     */
    private function getPerformanceData($filters)
    {
        $loanOfficers = User::whereHas('roles', fn($q) => $q->where('name', 'loan_officer'))
            ->with('branch')
            ->get()
            ->map(function($user) {
                return [
                    'name' => $user->name,
                    'branch' => $user->branch->name ?? 'N/A',
                    'loans_count' => Loan::where('created_by', $user->id)->count(),
                    'clients_count' => Client::where('created_by', $user->id)->count(),
                    'total_disbursed' => Loan::where('created_by', $user->id)->sum('amount'),
                ];
            });

        return [
            'loan_officers' => $loanOfficers,
            'monthly_targets' => $this->getMonthlyTargets(),
            'achievement_rates' => $this->getAchievementRates(),
        ];
    }

    /**
     * Get branch performance data
     */
    private function getBranchPerformanceData($filters)
    {
        return Branch::withCount(['loans', 'clients'])
            ->withSum('loans', 'principal_amount')
            ->get()
            ->map(function ($branch) {
                return [
                    'name' => $branch->name,
                    'loans_count' => $branch->loans_count,
                    'clients_count' => $branch->clients_count,
                    'total_portfolio' => $branch->loans_sum_principal_amount ?? 0,
                ];
            });
    }

    /**
     * Get client statistics
     */
    private function getClientStatistics($filters)
    {
        $query = Client::query()
            ->when($filters['branch_id'], fn($q) => $q->where('branch_id', $filters['branch_id']))
            ->when($filters['status'], fn($q) => $q->where('status', $filters['status']))
            ->when($filters['date_from'], fn($q) => $q->where('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn($q) => $q->where('created_at', '<=', $filters['date_to']));

        return [
            'total_clients' => $query->count(),
            'active_clients' => $query->where('status', 'active')->count(),
            'new_clients' => $query->whereMonth('created_at', now()->month)->count(),
            'average_loan_per_client' => $query->withCount('loans')->get()->avg('loans_count'),
        ];
    }

    /**
     * Get staff statistics
     */
    private function getStaffStatistics($filters)
    {
        $query = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'general_manager', 'branch_manager', 'loan_officer', 'hr']);
        })
        ->when($filters['branch_id'], fn($q) => $q->where('branch_id', $filters['branch_id']))
        ->when($filters['date_from'], fn($q) => $q->where('created_at', '>=', $filters['date_from']))
        ->when($filters['date_to'], fn($q) => $q->where('created_at', '<=', $filters['date_to']));

        return [
            'total_staff' => $query->count(),
            'loan_officers' => $query->whereHas('roles', fn($q) => $q->where('name', 'loan_officer'))->count(),
            'branch_managers' => $query->whereHas('roles', fn($q) => $q->where('name', 'branch_manager'))->count(),
            'total_loans_managed' => Loan::whereHas('user')->count(),
        ];
    }

    /**
     * Get monthly targets
     */
    private function getMonthlyTargets()
    {
        return [
            'loans_disbursed' => 50,
            'collections' => 100000,
            'new_clients' => 25,
        ];
    }

    /**
     * Get achievement rates
     */
    private function getAchievementRates()
    {
        return [
            'loans_disbursed' => 85,
            'collections' => 92,
            'new_clients' => 78,
        ];
    }

    // Export methods (simplified implementations)
    private function exportPortfolioPdf($filters) { return response()->json(['message' => 'PDF export not implemented']); }
    private function exportCollectionsPdf($filters) { return response()->json(['message' => 'PDF export not implemented']); }
    private function exportFinancialPdf($filters) { return response()->json(['message' => 'PDF export not implemented']); }
    private function exportPortfolioExcel($filters) { return response()->json(['message' => 'Excel export not implemented']); }
    private function exportCollectionsExcel($filters) { return response()->json(['message' => 'Excel export not implemented']); }
    private function exportClientsExcel($filters) { return response()->json(['message' => 'Excel export not implemented']); }
}
