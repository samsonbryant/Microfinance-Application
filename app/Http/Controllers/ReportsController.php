<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Client;
use App\Models\Branch;
use App\Models\Transaction;
use App\Models\LoanRepayment;
use App\Models\SavingsAccount;
use App\Models\GeneralLedger;
use App\Models\Collection;
use App\Models\RecoveryAction;
use App\Models\FinancialReport;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;
use Excel;
use App\Exports\PortfolioAtRiskExport;
use App\Exports\LoanPerformanceExport;
use App\Exports\FinancialSummaryExport;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        // Get available report types
        $reportTypes = [
            'portfolio_at_risk' => 'Portfolio at Risk',
            'loan_performance' => 'Loan Performance',
            'client_demographics' => 'Client Demographics',
            'financial_summary' => 'Financial Summary',
            'branch_performance' => 'Branch Performance',
            'collections_report' => 'Collections Report',
            'recovery_report' => 'Recovery Report',
            'audit_trail' => 'Audit Trail'
        ];

        // Get recent reports
        $recentReports = FinancialReport::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('reports.index', compact('reportTypes', 'recentReports'));
    }

    public function portfolioAtRisk(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $date = $request->get('date', now()->format('Y-m-d'));

        $query = $this->getBranchQuery($branchId, $user->role);

        // Get overdue loans
        $overdueLoans = $query(Loan::class)
            ->with(['client', 'branch'])
            ->where('status', 'overdue')
            ->where('due_date', '<=', $date)
            ->orderBy('due_date', 'asc')
            ->get();

        // Calculate PAR metrics
        $totalPortfolio = $query(Loan::class)
            ->whereIn('status', ['active', 'disbursed', 'overdue'])
            ->sum('outstanding_balance');

        $parAmount = $overdueLoans->sum('outstanding_balance');
        $parPercentage = $totalPortfolio > 0 ? ($parAmount / $totalPortfolio) * 100 : 0;

        // Age analysis
        $ageAnalysis = $overdueLoans->groupBy(function ($loan) {
            $daysOverdue = now()->diffInDays($loan->due_date);
            if ($daysOverdue <= 30) return '1-30 days';
            if ($daysOverdue <= 60) return '31-60 days';
            if ($daysOverdue <= 90) return '61-90 days';
            return '90+ days';
        })->map(function ($loans) {
            return [
                'count' => $loans->count(),
                'amount' => $loans->sum('outstanding_balance')
            ];
        });

        // Branch analysis (for admin users)
        $branchAnalysis = [];
        if ($user->role === 'admin') {
            $branchAnalysis = Branch::withCount(['loans' => function ($query) {
                $query->where('status', 'overdue');
            }])
            ->withSum(['loans as overdue_amount' => function ($query) {
                $query->where('status', 'overdue');
            }], 'outstanding_balance')
            ->get()
            ->map(function ($branch) {
                return [
                    'name' => $branch->name,
                    'overdue_count' => $branch->loans_count,
                    'overdue_amount' => $branch->overdue_amount ?? 0,
                    'par_percentage' => $branch->overdue_amount > 0 ? 
                        ($branch->overdue_amount / ($branch->overdue_amount + $branch->loans()->whereIn('status', ['active', 'disbursed'])->sum('outstanding_balance'))) * 100 : 0
                ];
            });
        }

        $branches = Branch::all();

        $data = [
            'overdue_loans' => $overdueLoans,
            'total_portfolio' => $totalPortfolio,
            'par_amount' => $parAmount,
            'par_percentage' => round($parPercentage, 2),
            'age_analysis' => $ageAnalysis,
            'branch_analysis' => $branchAnalysis,
            'branches' => $branches,
            'report_date' => $date
        ];

        if ($request->get('export') === 'pdf') {
            return $this->exportToPDF('reports.portfolio-at-risk', $data, 'Portfolio at Risk Report');
        }

        if ($request->get('export') === 'excel') {
            return Excel::download(new PortfolioAtRiskExport($data), 'portfolio-at-risk.xlsx');
        }

        return view('reports.portfolio-at-risk', $data);
    }

    public function loanPerformance(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $startDate = $request->get('start_date', now()->subYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $query = $this->getBranchQuery($branchId, $user->role);

        // Loan disbursements
        $disbursements = $query(Loan::class)
            ->whereBetween('disbursement_date', [$startDate, $endDate])
            ->whereNotNull('disbursement_date')
            ->get();

        // Repayments
        $repayments = $query(LoanRepayment::class)
            ->whereBetween('actual_payment_date', [$startDate, $endDate])
            ->whereNotNull('actual_payment_date')
            ->get();

        // Performance metrics
        $metrics = [
            'total_disbursed' => $disbursements->sum('amount'),
            'total_collected' => $repayments->sum('total_paid'),
            'total_loans' => $disbursements->count(),
            'average_loan_size' => $disbursements->avg('amount'),
            'collection_rate' => $disbursements->sum('amount') > 0 ? 
                ($repayments->sum('total_paid') / $disbursements->sum('amount')) * 100 : 0,
            'on_time_payments' => $repayments->where('days_overdue', 0)->count(),
            'overdue_payments' => $repayments->where('days_overdue', '>', 0)->count(),
        ];

        // Monthly trends
        $monthlyData = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current->lte($end)) {
            $month = $current->format('Y-m');
            $monthlyData[$month] = [
                'month' => $current->format('M Y'),
                'disbursed' => $disbursements->where('disbursement_date', '>=', $current->startOfMonth())
                    ->where('disbursement_date', '<=', $current->endOfMonth())
                    ->sum('amount'),
                'collected' => $repayments->where('actual_payment_date', '>=', $current->startOfMonth())
                    ->where('actual_payment_date', '<=', $current->endOfMonth())
                    ->sum('total_paid'),
            ];
            $current->addMonth();
        }

        // Loan type analysis
        $loanTypeAnalysis = $disbursements->groupBy('loan_type')->map(function ($loans) {
            return [
                'count' => $loans->count(),
                'amount' => $loans->sum('amount'),
                'average' => $loans->avg('amount')
            ];
        });

        $data = [
            'metrics' => $metrics,
            'monthly_data' => $monthlyData,
            'loan_type_analysis' => $loanTypeAnalysis,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if ($request->get('export') === 'pdf') {
            return $this->exportToPDF('reports.loan-performance', $data, 'Loan Performance Report');
        }

        if ($request->get('export') === 'excel') {
            return Excel::download(new LoanPerformanceExport($data), 'loan-performance.xlsx');
        }

        return view('reports.loan-performance', $data);
    }

    public function clientDemographics(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = $this->getBranchQuery($branchId, $user->role);

        $clients = $query(Client::class)->get();

        // Age groups
        $ageGroups = $clients->groupBy(function ($client) {
            $age = $client->date_of_birth ? now()->diffInYears($client->date_of_birth) : 0;
            if ($age < 25) return '18-24';
            if ($age < 35) return '25-34';
            if ($age < 45) return '35-44';
            if ($age < 55) return '45-54';
            return '55+';
        })->map->count();

        // Gender distribution
        $genderDistribution = $clients->groupBy('gender')->map->count();

        // Occupation analysis
        $occupationAnalysis = $clients->groupBy('occupation')->map->count();

        // Income analysis
        $incomeRanges = $clients->groupBy(function ($client) {
            $income = $client->monthly_income ?? 0;
            if ($income < 1000) return 'Under $1,000';
            if ($income < 2500) return '$1,000 - $2,500';
            if ($income < 5000) return '$2,500 - $5,000';
            return 'Over $5,000';
        })->map->count();

        // KYC status
        $kycStatus = $clients->groupBy('kyc_status')->map->count();

        // Client status
        $clientStatus = $clients->groupBy('status')->map->count();

        $data = [
            'total_clients' => $clients->count(),
            'age_groups' => $ageGroups,
            'gender_distribution' => $genderDistribution,
            'occupation_analysis' => $occupationAnalysis,
            'income_ranges' => $incomeRanges,
            'kyc_status' => $kycStatus,
            'client_status' => $clientStatus
        ];

        if ($request->get('export') === 'pdf') {
            return $this->exportToPDF('reports.client-demographics', $data, 'Client Demographics Report');
        }

        return view('reports.client-demographics', $data);
    }

    public function financialSummary(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $query = $this->getBranchQuery($branchId, $user->role);

        // Income
        $interestIncome = $query(Transaction::class)
            ->where('type', 'interest_posting')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $feeIncome = $query(Transaction::class)
            ->where('type', 'fee')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $penaltyIncome = $query(Transaction::class)
            ->where('type', 'penalty')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        // Expenses
        $operatingExpenses = $query(Transaction::class)
            ->whereIn('type', ['operating_expense', 'staff_salary', 'rent', 'utilities'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        // Assets
        $totalLoans = $query(Loan::class)
            ->whereIn('status', ['active', 'disbursed', 'overdue'])
            ->sum('outstanding_balance');

        $totalSavings = $query(SavingsAccount::class)
            ->where('status', 'active')
            ->sum('balance');

        $cashOnHand = $query(Transaction::class)
            ->where('type', 'deposit')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount') - 
            $query(Transaction::class)
            ->where('type', 'withdrawal')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $branches = Branch::all();

        $data = [
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'income' => [
                'interest' => $interestIncome,
                'fees' => $feeIncome,
                'penalties' => $penaltyIncome,
                'total' => $interestIncome + $feeIncome + $penaltyIncome
            ],
            'expenses' => [
                'operating' => $operatingExpenses,
                'total' => $operatingExpenses
            ],
            'assets' => [
                'loans' => $totalLoans,
                'savings' => $totalSavings,
                'cash' => $cashOnHand,
                'total' => $totalLoans + $totalSavings + $cashOnHand
            ],
            'profit_loss' => ($interestIncome + $feeIncome + $penaltyIncome) - $operatingExpenses,
            'branches' => $branches
        ];

        if ($request->get('export') === 'pdf') {
            return $this->exportToPDF('reports.financial-summary', $data, 'Financial Summary Report');
        }

        if ($request->get('export') === 'excel') {
            return Excel::download(new FinancialSummaryExport($data), 'financial-summary.xlsx');
        }

        return view('reports.financial-summary', $data);
    }

    public function branchPerformance(Request $request)
    {
        $user = auth()->user();
        
        if ($user->role !== 'admin') {
            abort(403, 'Unauthorized access to branch performance reports');
        }

        $branches = Branch::withCount(['clients', 'loans', 'savingsAccounts'])
            ->withSum('loans', 'outstanding_balance')
            ->withSum('savingsAccounts', 'balance')
            ->get()
            ->map(function ($branch) {
                $overdueLoans = $branch->loans()->where('status', 'overdue')->count();
                $totalLoans = $branch->loans_count;
                $parPercentage = $totalLoans > 0 ? ($overdueLoans / $totalLoans) * 100 : 0;

                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'code' => $branch->code,
                    'clients_count' => $branch->clients_count,
                    'loans_count' => $branch->loans_count,
                    'savings_accounts_count' => $branch->savings_accounts_count,
                    'loan_portfolio' => $branch->loans_sum_outstanding_balance ?? 0,
                    'savings_balance' => $branch->savings_accounts_sum_balance ?? 0,
                    'overdue_loans' => $overdueLoans,
                    'par_percentage' => round($parPercentage, 2),
                    'is_active' => $branch->is_active
                ];
            });

        $data = [
            'branches' => $branches,
            'total_branches' => $branches->count(),
            'active_branches' => $branches->where('is_active', true)->count(),
            'total_clients' => $branches->sum('clients_count'),
            'total_loans' => $branches->sum('loans_count'),
            'total_portfolio' => $branches->sum('loan_portfolio'),
            'total_savings' => $branches->sum('savings_balance')
        ];

        if ($request->get('export') === 'pdf') {
            return $this->exportToPDF('reports.branch-performance', $data, 'Branch Performance Report');
        }

        return view('reports.branch-performance', $data);
    }

    public function export($type)
    {
        // Implementation for various export formats
        return response()->json(['message' => 'Export functionality coming soon']);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'report_type' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'branch_id' => 'nullable|exists:branches,id',
            'format' => 'nullable|in:pdf,excel,csv'
        ]);

        $reportType = $request->report_type;
        $format = $request->format ?? 'pdf';

        // Generate report based on type
        switch ($reportType) {
            case 'portfolio-at-risk':
                return $this->portfolioAtRisk($request);
            case 'loan-performance':
                return $this->loanPerformance($request);
            case 'client-demographics':
                return $this->clientDemographics($request);
            case 'financial-summary':
                return $this->financialSummary($request);
            case 'branch-performance':
                return $this->branchPerformance($request);
            default:
                return response()->json(['success' => false, 'message' => 'Invalid report type'], 400);
        }
    }

    private function getBranchQuery($branchId, $userRole = null)
    {
        return function ($model) use ($branchId, $userRole) {
            $query = $model::query();
            
            if ($branchId && $userRole !== 'admin') {
                $query->where('branch_id', $branchId);
            }
            
            return $query;
        };
    }

    private function exportToPDF($view, $data, $title)
    {
        $pdf = PDF::loadView($view, $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download(strtolower(str_replace(' ', '-', $title)) . '.pdf');
    }
}
