<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\SavingsAccount;
use App\Models\Client;
use App\Models\Transaction;
use App\Models\GeneralLedgerEntry;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialAnalyticsService
{
    /**
     * Get comprehensive financial analytics
     */
    public function getComprehensiveAnalytics($branchId = null, $userId = null)
    {
        $loanQuery = $this->getLoanQuery($branchId, $userId);
        $transactionQuery = $this->getTransactionQuery($branchId, $userId);
        
        return [
            'loans_due_today' => $this->getLoansDueToday($loanQuery),
            'overdue_loans' => $this->getOverdueLoans($loanQuery),
            'active_loans' => $this->getActiveLoans($loanQuery),
            'loan_requests' => $this->getLoanRequests($loanQuery),
            'released_principal' => $this->getReleasedPrincipal($loanQuery),
            'outstanding_principal' => $this->getOutstandingPrincipal($loanQuery),
            'portfolio_at_risk' => $this->getPortfolioAtRisk($loanQuery),
            'interest_collected' => $this->getInterestCollected($transactionQuery),
            'realized_profit' => $this->getRealizedProfit($transactionQuery),
            'active_borrowers' => $this->getActiveBorrowers($branchId, $userId),
            'default_rate' => $this->getDefaultRate($loanQuery),
            'charged_fees' => $this->getChargedFees($transactionQuery),
            'penalties_collected' => $this->getPenaltiesCollected($transactionQuery),
            'pending_loans' => $this->getPendingLoans($loanQuery),
            'repayments_collected' => $this->getRepaymentsCollected($transactionQuery),
            'average_loan_size' => $this->getAverageLoanSize($loanQuery),
            'expected_profit' => $this->getExpectedProfit($loanQuery),
            'loan_release_vs_completed' => $this->getLoanReleaseVsCompleted($loanQuery),
            'monthly_trends' => $this->getMonthlyTrends($loanQuery, $transactionQuery),
            'detailed_breakdowns' => $this->getDetailedBreakdowns($loanQuery, $transactionQuery)
        ];
    }

    /**
     * Get loans due today
     */
    private function getLoansDueToday($loanQuery)
    {
        $loans = $loanQuery->whereIn('status', ['active', 'disbursed'])
            ->where('next_due_date', today())->get();
        
        return [
            'count' => $loans->count(),
            'amount' => $loans->sum('monthly_payment') ?? 0,
            'loans' => $loans->map(function($loan) {
                return [
                    'id' => $loan->id,
                    'client_name' => $loan->client->full_name ?? 'N/A',
                    'amount' => $loan->monthly_payment ?? 0,
                    'outstanding' => $loan->outstanding_balance ?? 0,
                    'next_payment_date' => $loan->next_due_date
                ];
            })
        ];
    }

    /**
     * Get overdue loans
     */
    private function getOverdueLoans($loanQuery)
    {
        // Find loans with next_due_date in the past
        $loans = $loanQuery->whereIn('status', ['active', 'disbursed', 'overdue'])
            ->where('next_due_date', '<', now())
            ->where('outstanding_balance', '>', 0)
            ->get();
        
        return [
            'count' => $loans->count(),
            'amount' => $loans->sum('outstanding_balance') ?? 0,
            'percentage' => $this->calculatePercentage($loans->count(), $loanQuery->count()),
            'loans' => $loans->map(function($loan) {
                return [
                    'id' => $loan->id,
                    'client_name' => $loan->client->full_name ?? 'N/A',
                    'amount' => $loan->outstanding_balance ?? 0,
                    'days_overdue' => $this->getDaysOverdue($loan),
                    'last_payment_date' => $loan->next_due_date
                ];
            })
        ];
    }

    /**
     * Get active loans
     */
    private function getActiveLoans($loanQuery)
    {
        $loans = $loanQuery->whereIn('status', ['active', 'disbursed'])->get();
        
        return [
            'count' => $loans->count(),
            'amount' => $loans->sum('amount') ?? 0,
            'outstanding' => $loans->sum('outstanding_balance') ?? 0,
            'percentage' => $this->calculatePercentage($loans->count(), $loanQuery->count())
        ];
    }

    /**
     * Get loan requests
     */
    private function getLoanRequests($loanQuery)
    {
        $requests = $loanQuery->where('status', 'pending')->get();
        
        return [
            'count' => $requests->count(),
            'amount' => $requests->sum('amount'),
            'percentage' => $this->calculatePercentage($requests->count(), $loanQuery->count())
        ];
    }

    /**
     * Get released principal
     */
    private function getReleasedPrincipal($loanQuery)
    {
        $released = $loanQuery->whereIn('status', ['active', 'disbursed', 'overdue', 'completed'])
            ->whereNotNull('disbursement_date')
            ->sum('amount') ?? 0;
        
        return [
            'total' => $released,
            'this_month' => $loanQuery->whereIn('status', ['active', 'disbursed', 'overdue', 'completed'])
                ->whereNotNull('disbursement_date')
                ->whereMonth('disbursement_date', now()->month)
                ->whereYear('disbursement_date', now()->year)
                ->sum('amount') ?? 0,
            'last_month' => $loanQuery->whereIn('status', ['active', 'disbursed', 'overdue', 'completed'])
                ->whereNotNull('disbursement_date')
                ->whereMonth('disbursement_date', now()->subMonth()->month)
                ->whereYear('disbursement_date', now()->subMonth()->year)
                ->sum('amount') ?? 0
        ];
    }

    /**
     * Get outstanding principal
     */
    private function getOutstandingPrincipal($loanQuery)
    {
        $outstanding = $loanQuery->whereIn('status', ['active', 'disbursed', 'overdue'])
            ->sum('outstanding_balance') ?? 0;
        
        return [
            'total' => $outstanding,
            'active_loans' => $loanQuery->whereIn('status', ['active', 'disbursed'])
                ->sum('outstanding_balance') ?? 0,
            'overdue_loans' => $loanQuery->where('next_due_date', '<', now())
                ->whereIn('status', ['active', 'disbursed', 'overdue'])
                ->sum('outstanding_balance') ?? 0
        ];
    }

    /**
     * Get Portfolio at Risk (PAR)
     */
    private function getPortfolioAtRisk($loanQuery)
    {
        $totalOutstanding = $loanQuery->whereIn('status', ['active', 'disbursed', 'overdue'])
            ->sum('outstanding_balance') ?? 0;
        
        $par14 = $loanQuery->whereIn('status', ['active', 'disbursed', 'overdue'])
            ->where('next_due_date', '<=', now()->subDays(14))
            ->where('next_due_date', '<', now())
            ->sum('outstanding_balance') ?? 0;
            
        $par30 = $loanQuery->whereIn('status', ['active', 'disbursed', 'overdue'])
            ->where('next_due_date', '<=', now()->subDays(30))
            ->sum('outstanding_balance') ?? 0;
            
        $parOver30 = $loanQuery->whereIn('status', ['active', 'disbursed', 'overdue'])
            ->where('next_due_date', '<=', now()->subDays(30))
            ->sum('outstanding_balance') ?? 0;

        return [
            '14_day_par' => [
                'amount' => $par14,
                'percentage' => $totalOutstanding > 0 ? ($par14 / $totalOutstanding) * 100 : 0
            ],
            '30_day_par' => [
                'amount' => $par30,
                'percentage' => $totalOutstanding > 0 ? ($par30 / $totalOutstanding) * 100 : 0
            ],
            'over_30_day_par' => [
                'amount' => $parOver30,
                'percentage' => $totalOutstanding > 0 ? ($parOver30 / $totalOutstanding) * 100 : 0
            ],
            'total_par' => [
                'amount' => $parOver30,
                'percentage' => $totalOutstanding > 0 ? ($parOver30 / $totalOutstanding) * 100 : 0
            ]
        ];
    }

    /**
     * Get interest collected
     */
    private function getInterestCollected($transactionQuery)
    {
        $interest = $transactionQuery->where('type', 'interest_payment')->sum('amount');
        
        return [
            'total' => $interest,
            'this_month' => $transactionQuery->where('type', 'interest_payment')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'last_month' => $transactionQuery->where('type', 'interest_payment')
                ->whereMonth('created_at', now()->subMonth()->month)
                ->sum('amount')
        ];
    }

    /**
     * Get realized profit
     */
    private function getRealizedProfit($transactionQuery)
    {
        $interest = $transactionQuery->where('type', 'interest_payment')->sum('amount');
        $fees = $transactionQuery->where('type', 'fee')->sum('amount');
        $penalties = $transactionQuery->where('type', 'penalty')->sum('amount');
        
        return [
            'total' => $interest + $fees + $penalties,
            'interest' => $interest,
            'fees' => $fees,
            'penalties' => $penalties
        ];
    }

    /**
     * Get active borrowers
     */
    private function getActiveBorrowers($branchId = null, $userId = null)
    {
        $query = Client::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        
        if ($userId) {
            $query->where('created_by', $userId);
        }
        
        $activeBorrowers = $query->whereHas('loans', function($q) {
            $q->whereIn('status', ['active', 'disbursed', 'overdue']);
        })->count();
        
        $totalClients = $query->count();
        
        return [
            'count' => $activeBorrowers,
            'total_clients' => $totalClients,
            'percentage' => $totalClients > 0 ? ($activeBorrowers / $totalClients) * 100 : 0
        ];
    }

    /**
     * Get default rate
     */
    private function getDefaultRate($loanQuery)
    {
        $totalLoans = $loanQuery->count();
        $defaultedLoans = $loanQuery->where('status', 'defaulted')->count();
        
        return [
            'count' => $defaultedLoans,
            'percentage' => $totalLoans > 0 ? ($defaultedLoans / $totalLoans) * 100 : 0
        ];
    }

    /**
     * Get charged fees
     */
    private function getChargedFees($transactionQuery)
    {
        $fees = $transactionQuery->where('type', 'fee')->sum('amount');
        
        return [
            'total' => $fees,
            'this_month' => $transactionQuery->where('type', 'fee')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'last_month' => $transactionQuery->where('type', 'fee')
                ->whereMonth('created_at', now()->subMonth()->month)
                ->sum('amount')
        ];
    }

    /**
     * Get penalties collected
     */
    private function getPenaltiesCollected($transactionQuery)
    {
        $penalties = $transactionQuery->where('type', 'penalty')->sum('amount');
        
        return [
            'total' => $penalties,
            'this_month' => $transactionQuery->where('type', 'penalty')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'last_month' => $transactionQuery->where('type', 'penalty')
                ->whereMonth('created_at', now()->subMonth()->month)
                ->sum('amount')
        ];
    }

    /**
     * Get pending loans
     */
    private function getPendingLoans($loanQuery)
    {
        $pending = $loanQuery->where('status', 'pending')->get();
        
        return [
            'count' => $pending->count(),
            'amount' => $pending->sum('amount'),
            'percentage' => $this->calculatePercentage($pending->count(), $loanQuery->count())
        ];
    }

    /**
     * Get repayments collected
     */
    private function getRepaymentsCollected($transactionQuery)
    {
        $repayments = $transactionQuery->where('type', 'loan_repayment')->sum('amount') ?? 0;
        
        return [
            'total' => $repayments,
            'this_month' => $transactionQuery->where('type', 'loan_repayment')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount') ?? 0,
            'last_month' => $transactionQuery->where('type', 'loan_repayment')
                ->whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->sum('amount') ?? 0
        ];
    }

    /**
     * Get average loan size
     */
    private function getAverageLoanSize($loanQuery)
    {
        $loans = $loanQuery->whereIn('status', ['active', 'overdue', 'completed'])->get();
        
        if ($loans->count() === 0) {
            return [
                'average' => 0,
                'median' => 0,
                'min' => 0,
                'max' => 0
            ];
        }
        
        return [
            'average' => $loans->avg('amount'),
            'median' => $this->calculateMedian($loans->pluck('amount')->toArray()),
            'min' => $loans->min('amount') ?? 0,
            'max' => $loans->max('amount') ?? 0
        ];
    }

    /**
     * Get expected profit
     */
    private function getExpectedProfit($loanQuery)
    {
        $activeLoans = $loanQuery->where('status', 'active')->get();
        $expectedInterest = $activeLoans->sum(function($loan) {
            return $loan->amount * ($loan->interest_rate / 100);
        });
        
        return [
            'total' => $expectedInterest,
            'monthly' => $expectedInterest / 12,
            'by_loan' => $activeLoans->map(function($loan) {
                return [
                    'loan_id' => $loan->id,
                    'expected_interest' => $loan->amount * ($loan->interest_rate / 100)
                ];
            })
        ];
    }

    /**
     * Get loan release vs completed vs defaulted
     */
    private function getLoanReleaseVsCompleted($loanQuery)
    {
        $released = $loanQuery->whereIn('status', ['active', 'overdue', 'completed', 'defaulted'])->count();
        $completed = $loanQuery->where('status', 'completed')->count();
        $defaulted = $loanQuery->where('status', 'defaulted')->count();
        
        return [
            'released' => $released,
            'completed' => $completed,
            'defaulted' => $defaulted,
            'completion_rate' => $released > 0 ? ($completed / $released) * 100 : 0,
            'default_rate' => $released > 0 ? ($defaulted / $released) * 100 : 0
        ];
    }

    /**
     * Get monthly trends for 12 months
     */
    private function getMonthlyTrends($loanQuery, $transactionQuery)
    {
        $trends = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('M Y');
            
            $trends[] = [
                'month' => $month,
                'loans_released' => $loanQuery->whereMonth('disbursement_date', $date->month)
                    ->whereYear('disbursement_date', $date->year)
                    ->sum('amount') ?? 0,
                'loans_completed' => $loanQuery->where('status', 'completed')
                    ->whereMonth('updated_at', $date->month)
                    ->whereYear('updated_at', $date->year)
                    ->count(),
                'loans_defaulted' => $loanQuery->where('status', 'defaulted')
                    ->whereMonth('updated_at', $date->month)
                    ->whereYear('updated_at', $date->year)
                    ->count(),
                'repayments_collected' => $transactionQuery->where('type', 'repayment')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('amount') ?? 0,
                'interest_collected' => $transactionQuery->where('type', 'interest_payment')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('amount') ?? 0,
                'expected_profit' => $this->calculateExpectedProfitForMonth($loanQuery, $date)
            ];
        }
        
        return $trends;
    }

    /**
     * Get detailed breakdowns
     */
    private function getDetailedBreakdowns($loanQuery, $transactionQuery)
    {
        return [
            'loans_by_status' => $this->getLoansByStatus($loanQuery),
            'loans_by_size' => $this->getLoansBySize($loanQuery),
            'repayments_by_month' => $this->getRepaymentsByMonth($transactionQuery),
            'interest_by_month' => $this->getInterestByMonth($transactionQuery),
            'fees_by_type' => $this->getFeesByType($transactionQuery)
        ];
    }

    // Helper methods
    private function getLoanQuery($branchId = null, $userId = null)
    {
        $query = Loan::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        
        if ($userId) {
            $query->where('created_by', $userId);
        }
        
        return $query;
    }

    private function getTransactionQuery($branchId = null, $userId = null)
    {
        $query = Transaction::query();
        
        if ($branchId) {
            $query->whereHas('loan', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        
        if ($userId) {
            $query->whereHas('loan', function($q) use ($userId) {
                $q->where('created_by', $userId);
            });
        }
        
        return $query;
    }

    private function calculatePercentage($value, $total)
    {
        return $total > 0 ? ($value / $total) * 100 : 0;
    }

    private function getDaysOverdue($loan)
    {
        if ($loan->next_due_date && $loan->next_due_date < now()) {
            return now()->diffInDays($loan->next_due_date);
        }
        return 0;
    }

    private function calculateMedian($array)
    {
        if (empty($array)) {
            return 0;
        }
        
        sort($array);
        $count = count($array);
        $middle = floor(($count - 1) / 2);
        
        if ($count % 2) {
            return $array[$middle];
        } else {
            // Ensure we don't access out-of-bounds indices
            if (isset($array[$middle + 1])) {
                return ($array[$middle] + $array[$middle + 1]) / 2;
            } else {
                return $array[$middle];
            }
        }
    }

    private function calculateExpectedProfitForMonth($loanQuery, $date)
    {
        $activeLoans = $loanQuery->where('status', 'active')
            ->where('disbursement_date', '<=', $date->endOfMonth())
            ->get();
            
        if ($activeLoans->count() === 0) {
            return 0;
        }
            
        return $activeLoans->sum(function($loan) {
            return ($loan->amount ?? 0) * (($loan->interest_rate ?? 0) / 100);
        });
    }

    private function getLoansByStatus($loanQuery)
    {
        return $loanQuery->selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('status')
            ->get();
    }

    private function getLoansBySize($loanQuery)
    {
        return $loanQuery->selectRaw('
            CASE 
                WHEN amount < 1000 THEN "Small (<$1K)"
                WHEN amount < 5000 THEN "Medium ($1K-$5K)"
                WHEN amount < 10000 THEN "Large ($5K-$10K)"
                ELSE "Very Large (>$10K)"
            END as size_category,
            COUNT(*) as count,
            SUM(amount) as total_amount
        ')
        ->groupBy('size_category')
        ->get();
    }

    private function getRepaymentsByMonth($transactionQuery)
    {
        return $transactionQuery->where('type', 'repayment')
            ->selectRaw('strftime("%m", created_at) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    private function getInterestByMonth($transactionQuery)
    {
        return $transactionQuery->where('type', 'interest_payment')
            ->selectRaw('strftime("%m", created_at) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    private function getFeesByType($transactionQuery)
    {
        return $transactionQuery->where('type', 'fee')
            ->selectRaw('description, SUM(amount) as total')
            ->groupBy('description')
            ->get();
    }
}
