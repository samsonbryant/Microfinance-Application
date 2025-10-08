<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\GeneralLedgerEntry;
use App\Models\Loan;
use App\Models\SavingsAccount;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialReportsService
{
    /**
     * Generate Profit & Loss Statement
     */
    public function generateProfitAndLoss($startDate, $endDate, $basis = 'cash')
    {
        $query = GeneralLedgerEntry::where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        // Cash basis: only actual cash transactions
        if ($basis === 'cash') {
            $query->whereIn('reference_type', ['loan_repayment', 'savings_deposit', 'savings_withdrawal', 'expense_entry']);
        }
        // Accrual basis: includes accruals
        else {
            $query->whereIn('reference_type', ['loan_repayment', 'savings_deposit', 'savings_withdrawal', 'expense_entry', 'interest_accrual', 'savings_interest_accrual']);
        }

        $entries = $query->with('account')->get();

        // Revenue
        $revenue = $entries->where('account.type', 'revenue')->groupBy('account_id')->map(function ($accountEntries, $accountId) {
            $account = $accountEntries->first()->account;
            $total = $accountEntries->sum('credit') - $accountEntries->sum('debit');
            return [
                'account' => $account,
                'amount' => $total,
                'formatted_amount' => '$' . number_format($total, 2)
            ];
        })->filter(function ($item) {
            return $item['amount'] > 0;
        });

        // Expenses
        $expenses = $entries->where('account.type', 'expense')->groupBy('account_id')->map(function ($accountEntries, $accountId) {
            $account = $accountEntries->first()->account;
            $total = $accountEntries->sum('debit') - $accountEntries->sum('credit');
            return [
                'account' => $account,
                'amount' => $total,
                'formatted_amount' => '$' . number_format($total, 2)
            ];
        })->filter(function ($item) {
            return $item['amount'] > 0;
        });

        $totalRevenue = $revenue->sum('amount');
        $totalExpenses = $expenses->sum('amount');
        $netIncome = $totalRevenue - $totalExpenses;

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'basis' => $basis
            ],
            'revenue' => $revenue,
            'expenses' => $expenses,
            'totals' => [
                'total_revenue' => $totalRevenue,
                'total_expenses' => $totalExpenses,
                'net_income' => $netIncome
            ],
            'formatted_totals' => [
                'total_revenue' => '$' . number_format($totalRevenue, 2),
                'total_expenses' => '$' . number_format($totalExpenses, 2),
                'net_income' => '$' . number_format($netIncome, 2)
            ]
        ];
    }

    /**
     * Generate Balance Sheet
     */
    public function generateBalanceSheet($asOfDate = null)
    {
        $asOfDate = $asOfDate ?? now()->toDateString();

        // Assets
        $assets = ChartOfAccount::where('type', 'asset')
            ->where('is_active', true)
            ->get()
            ->map(function ($account) use ($asOfDate) {
                $balance = GeneralLedgerEntry::getBalanceForAccount($account->id, $asOfDate);
                return [
                    'account' => $account,
                    'balance' => $balance,
                    'formatted_balance' => '$' . number_format($balance, 2)
                ];
            })
            ->filter(function ($item) {
                return $item['balance'] != 0;
            })
            ->groupBy('account.category');

        // Liabilities
        $liabilities = ChartOfAccount::where('type', 'liability')
            ->where('is_active', true)
            ->get()
            ->map(function ($account) use ($asOfDate) {
                $balance = GeneralLedgerEntry::getBalanceForAccount($account->id, $asOfDate);
                return [
                    'account' => $account,
                    'balance' => $balance,
                    'formatted_balance' => '$' . number_format($balance, 2)
                ];
            })
            ->filter(function ($item) {
                return $item['balance'] != 0;
            })
            ->groupBy('account.category');

        // Owner's Equity
        $equity = ChartOfAccount::where('type', 'equity')
            ->where('is_active', true)
            ->get()
            ->map(function ($account) use ($asOfDate) {
                $balance = GeneralLedgerEntry::getBalanceForAccount($account->id, $asOfDate);
                return [
                    'account' => $account,
                    'balance' => $balance,
                    'formatted_balance' => '$' . number_format($balance, 2)
                ];
            })
            ->filter(function ($item) {
                return $item['balance'] != 0;
            })
            ->groupBy('account.category');

        $totalAssets = $assets->flatten()->sum('balance');
        $totalLiabilities = $liabilities->flatten()->sum('balance');
        $totalEquity = $equity->flatten()->sum('balance');

        return [
            'as_of_date' => $asOfDate,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'totals' => [
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'total_liabilities_equity' => $totalLiabilities + $totalEquity
            ],
            'formatted_totals' => [
                'total_assets' => '$' . number_format($totalAssets, 2),
                'total_liabilities' => '$' . number_format($totalLiabilities, 2),
                'total_equity' => '$' . number_format($totalEquity, 2),
                'total_liabilities_equity' => '$' . number_format($totalLiabilities + $totalEquity, 2)
            ],
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01
        ];
    }

    /**
     * Generate Cash Flow Statement
     */
    public function generateCashFlowStatement($startDate, $endDate)
    {
        $entries = GeneralLedgerEntry::where('status', 'approved')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->whereHas('account', function ($query) {
                $query->whereIn('code', ['1000', '1100']); // Cash accounts only
            })
            ->with('account')
            ->get();

        // Operating Activities
        $operatingActivities = $entries->whereIn('reference_type', [
            'loan_repayment', 'savings_deposit', 'savings_withdrawal', 'expense_entry'
        ])->groupBy('reference_type')->map(function ($group, $type) {
            $cashIn = $group->sum('debit');
            $cashOut = $group->sum('credit');
            return [
                'type' => $type,
                'description' => $this->getActivityDescription($type),
                'cash_in' => $cashIn,
                'cash_out' => $cashOut,
                'net_cash' => $cashIn - $cashOut
            ];
        });

        // Investing Activities
        $investingActivities = $entries->whereIn('reference_type', [
            'loan', 'property_purchase'
        ])->groupBy('reference_type')->map(function ($group, $type) {
            $cashIn = $group->sum('debit');
            $cashOut = $group->sum('credit');
            return [
                'type' => $type,
                'description' => $this->getActivityDescription($type),
                'cash_in' => $cashIn,
                'cash_out' => $cashOut,
                'net_cash' => $cashIn - $cashOut
            ];
        });

        // Financing Activities
        $financingActivities = $entries->whereIn('reference_type', [
            'capital_injection', 'shareholder_loan'
        ])->groupBy('reference_type')->map(function ($group, $type) {
            $cashIn = $group->sum('debit');
            $cashOut = $group->sum('credit');
            return [
                'type' => $type,
                'description' => $this->getActivityDescription($type),
                'cash_in' => $cashIn,
                'cash_out' => $cashOut,
                'net_cash' => $cashIn - $cashOut
            ];
        });

        $netOperatingCash = $operatingActivities->sum('net_cash');
        $netInvestingCash = $investingActivities->sum('net_cash');
        $netFinancingCash = $financingActivities->sum('net_cash');
        $netCashFlow = $netOperatingCash + $netInvestingCash + $netFinancingCash;

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'operating_activities' => $operatingActivities,
            'investing_activities' => $investingActivities,
            'financing_activities' => $financingActivities,
            'totals' => [
                'net_operating_cash' => $netOperatingCash,
                'net_investing_cash' => $netInvestingCash,
                'net_financing_cash' => $netFinancingCash,
                'net_cash_flow' => $netCashFlow
            ],
            'formatted_totals' => [
                'net_operating_cash' => '$' . number_format($netOperatingCash, 2),
                'net_investing_cash' => '$' . number_format($netInvestingCash, 2),
                'net_financing_cash' => '$' . number_format($netFinancingCash, 2),
                'net_cash_flow' => '$' . number_format($netCashFlow, 2)
            ]
        ];
    }

    /**
     * Generate Trial Balance
     */
    public function generateTrialBalance($asOfDate = null)
    {
        $trialBalance = GeneralLedgerEntry::getTrialBalance($asOfDate);
        
        $totalDebits = collect($trialBalance)->sum('debit');
        $totalCredits = collect($trialBalance)->sum('credit');

        return [
            'as_of_date' => $asOfDate ?? now()->toDateString(),
            'entries' => $trialBalance,
            'totals' => [
                'total_debits' => $totalDebits,
                'total_credits' => $totalCredits
            ],
            'formatted_totals' => [
                'total_debits' => '$' . number_format($totalDebits, 2),
                'total_credits' => '$' . number_format($totalCredits, 2)
            ],
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.01
        ];
    }

    /**
     * Get loan portfolio aging report
     */
    public function getLoanPortfolioAging()
    {
        $loans = Loan::whereIn('status', ['active', 'overdue'])
            ->with(['client', 'repayments' => function ($query) {
                $query->orderBy('due_date', 'desc');
            }])
            ->get();

        $aging = [
            'current' => [],
            '30_days' => [],
            '60_days' => [],
            '90_days' => [],
            'over_90_days' => []
        ];

        foreach ($loans as $loan) {
            $daysPastDue = $loan->days_past_due ?? 0;
            $loanData = [
                'loan' => $loan,
                'outstanding_balance' => $loan->outstanding_balance,
                'days_past_due' => $daysPastDue
            ];

            if ($daysPastDue <= 30) {
                $aging['current'][] = $loanData;
            } elseif ($daysPastDue <= 60) {
                $aging['30_days'][] = $loanData;
            } elseif ($daysPastDue <= 90) {
                $aging['60_days'][] = $loanData;
            } elseif ($daysPastDue <= 120) {
                $aging['90_days'][] = $loanData;
            } else {
                $aging['over_90_days'][] = $loanData;
            }
        }

        return $aging;
    }

    /**
     * Calculate loan loss provision
     */
    public function calculateLoanLossProvision()
    {
        $aging = $this->getLoanPortfolioAging();
        
        $provisionRates = [
            'current' => 0.01,    // 1%
            '30_days' => 0.05,    // 5%
            '60_days' => 0.10,    // 10%
            '90_days' => 0.25,    // 25%
            'over_90_days' => 0.50 // 50%
        ];

        $totalProvision = 0;
        $provisions = [];

        foreach ($aging as $bucket => $loans) {
            $bucketTotal = collect($loans)->sum('outstanding_balance');
            $provisionAmount = $bucketTotal * $provisionRates[$bucket];
            $totalProvision += $provisionAmount;

            $provisions[$bucket] = [
                'bucket' => $bucket,
                'loan_count' => count($loans),
                'total_outstanding' => $bucketTotal,
                'provision_rate' => $provisionRates[$bucket] * 100,
                'provision_amount' => $provisionAmount,
                'formatted_outstanding' => '$' . number_format($bucketTotal, 2),
                'formatted_provision' => '$' . number_format($provisionAmount, 2)
            ];
        }

        return [
            'provisions' => $provisions,
            'total_provision' => $totalProvision,
            'formatted_total_provision' => '$' . number_format($totalProvision, 2)
        ];
    }

    /**
     * Get financial dashboard data
     */
    public function getDashboardData($branchId = null)
    {
        $query = GeneralLedgerEntry::where('status', 'approved');
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Current month data
        $currentMonth = $query->whereBetween('transaction_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ])->get();

        // Previous month data
        $previousMonth = $query->whereBetween('transaction_date', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        ])->get();

        // Revenue
        $currentRevenue = $currentMonth->where('account.type', 'revenue')->sum('credit') - $currentMonth->where('account.type', 'revenue')->sum('debit');
        $previousRevenue = $previousMonth->where('account.type', 'revenue')->sum('credit') - $previousMonth->where('account.type', 'revenue')->sum('debit');

        // Expenses
        $currentExpenses = $currentMonth->where('account.type', 'expense')->sum('debit') - $currentMonth->where('account.type', 'expense')->sum('credit');
        $previousExpenses = $previousMonth->where('account.type', 'expense')->sum('debit') - $previousMonth->where('account.type', 'expense')->sum('credit');

        // Net Income
        $currentNetIncome = $currentRevenue - $currentExpenses;
        $previousNetIncome = $previousRevenue - $previousExpenses;

        return [
            'revenue' => [
                'current' => $currentRevenue,
                'previous' => $previousRevenue,
                'change' => $currentRevenue - $previousRevenue,
                'change_percentage' => $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0
            ],
            'expenses' => [
                'current' => $currentExpenses,
                'previous' => $previousExpenses,
                'change' => $currentExpenses - $previousExpenses,
                'change_percentage' => $previousExpenses > 0 ? (($currentExpenses - $previousExpenses) / $previousExpenses) * 100 : 0
            ],
            'net_income' => [
                'current' => $currentNetIncome,
                'previous' => $previousNetIncome,
                'change' => $currentNetIncome - $previousNetIncome,
                'change_percentage' => $previousNetIncome > 0 ? (($currentNetIncome - $previousNetIncome) / $previousNetIncome) * 100 : 0
            ]
        ];
    }

    /**
     * Get activity description for cash flow
     */
    private function getActivityDescription($type)
    {
        return match($type) {
            'loan_repayment' => 'Loan Repayments',
            'savings_deposit' => 'Savings Deposits',
            'savings_withdrawal' => 'Savings Withdrawals',
            'expense_entry' => 'Operating Expenses',
            'loan' => 'Loan Disbursements',
            'property_purchase' => 'Property & Equipment',
            'capital_injection' => 'Capital Contributions',
            'shareholder_loan' => 'Shareholder Loans',
            default => ucwords(str_replace('_', ' ', $type))
        };
    }
}
