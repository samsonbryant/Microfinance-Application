<?php

namespace App\Services;

use App\Models\GeneralLedgerEntry;
use App\Models\ChartOfAccount;
use App\Models\Loan;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Create double-entry accounting entries
     */
    public function createDoubleEntry($debitAccountId, $creditAccountId, $amount, $description, $referenceId = null, $referenceType = null, $branchId = null, $userId = null, $transactionDate = null)
    {
        $entryNumber = GeneralLedgerEntry::generateEntryNumber();
        $transactionDate = $transactionDate ?? now()->toDateString();
        
        DB::transaction(function () use ($debitAccountId, $creditAccountId, $amount, $description, $referenceId, $referenceType, $branchId, $userId, $transactionDate, $entryNumber) {
            // Debit entry
            GeneralLedgerEntry::create([
                'entry_number' => $entryNumber,
                'account_id' => $debitAccountId,
                'branch_id' => $branchId,
                'user_id' => $userId,
                'transaction_date' => $transactionDate,
                'debit' => $amount,
                'credit' => 0,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            // Credit entry
            GeneralLedgerEntry::create([
                'entry_number' => $entryNumber,
                'account_id' => $creditAccountId,
                'branch_id' => $branchId,
                'user_id' => $userId,
                'transaction_date' => $transactionDate,
                'debit' => 0,
                'credit' => $amount,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'status' => 'approved',
                'approved_at' => now(),
            ]);
        });
    }

    /**
     * Process loan disbursement accounting
     */
    public function processLoanDisbursement($loan, $amount, $branchId = null, $userId = null)
    {
        $loanReceivableAccount = ChartOfAccount::where('code', '1200')->first(); // Loan Portfolio
        $cashAccount = ChartOfAccount::where('code', '1000')->first(); // Cash on Hand

        if ($loanReceivableAccount && $cashAccount) {
            $this->createDoubleEntry(
                $loanReceivableAccount->id,
                $cashAccount->id,
                $amount,
                "Loan disbursement - {$loan->loan_number}",
                $loan->id,
                'loan',
                $branchId,
                $userId
            );
        }
    }

    /**
     * Process loan repayment accounting
     */
    public function processLoanRepayment($loan, $principalAmount, $interestAmount, $penaltyAmount = 0, $branchId = null, $userId = null)
    {
        $cashAccount = ChartOfAccount::where('code', '1000')->first(); // Cash on Hand
        $loanReceivableAccount = ChartOfAccount::where('code', '1200')->first(); // Loan Portfolio
        $interestIncomeAccount = ChartOfAccount::where('code', '4000')->first(); // Loan Interest Income
        $penaltyIncomeAccount = ChartOfAccount::where('code', '4100')->first(); // Penalty Income

        if ($cashAccount && $loanReceivableAccount && $interestIncomeAccount) {
            // Cash received (Debit)
            // Loan Portfolio reduced (Credit)
            if ($principalAmount > 0) {
                $this->createDoubleEntry(
                    $cashAccount->id,
                    $loanReceivableAccount->id,
                    $principalAmount,
                    "Loan principal repayment - {$loan->loan_number}",
                    $loan->id,
                    'loan_repayment',
                    $branchId,
                    $userId
                );
            }

            // Cash received (Debit)
            // Interest Income (Credit)
            if ($interestAmount > 0) {
                $this->createDoubleEntry(
                    $cashAccount->id,
                    $interestIncomeAccount->id,
                    $interestAmount,
                    "Interest payment - {$loan->loan_number}",
                    $loan->id,
                    'loan_repayment',
                    $branchId,
                    $userId
                );
            }

            // Cash received (Debit)
            // Penalty Income (Credit)
            if ($penaltyAmount > 0 && $penaltyIncomeAccount) {
                $this->createDoubleEntry(
                    $cashAccount->id,
                    $penaltyIncomeAccount->id,
                    $penaltyAmount,
                    "Penalty payment - {$loan->loan_number}",
                    $loan->id,
                    'loan_repayment',
                    $branchId,
                    $userId
                );
            }
        }
    }

    /**
     * Process savings deposit accounting
     */
    public function processSavingsDeposit($savingsAccount, $amount, $branchId = null, $userId = null)
    {
        $cashAccount = ChartOfAccount::where('code', '1000')->first(); // Cash on Hand
        $clientSavingsAccount = ChartOfAccount::where('code', '2000')->first(); // Client Savings

        if ($cashAccount && $clientSavingsAccount) {
            $this->createDoubleEntry(
                $cashAccount->id,
                $clientSavingsAccount->id,
                $amount,
                "Savings deposit - {$savingsAccount->account_number}",
                $savingsAccount->id,
                'savings_deposit',
                $branchId,
                $userId
            );
        }
    }

    /**
     * Process savings withdrawal accounting
     */
    public function processSavingsWithdrawal($savingsAccount, $amount, $branchId = null, $userId = null)
    {
        $cashAccount = ChartOfAccount::where('code', '1000')->first(); // Cash on Hand
        $clientSavingsAccount = ChartOfAccount::where('code', '2000')->first(); // Client Savings

        if ($cashAccount && $clientSavingsAccount) {
            $this->createDoubleEntry(
                $clientSavingsAccount->id,
                $cashAccount->id,
                $amount,
                "Savings withdrawal - {$savingsAccount->account_number}",
                $savingsAccount->id,
                'savings_withdrawal',
                $branchId,
                $userId
            );
        }
    }

    /**
     * Process interest accrual for loans (accrual basis)
     */
    public function processInterestAccrual($loan, $interestAmount, $branchId = null, $userId = null)
    {
        $loanReceivableAccount = ChartOfAccount::where('code', '1200')->first(); // Loan Portfolio
        $interestIncomeAccount = ChartOfAccount::where('code', '4000')->first(); // Loan Interest Income

        if ($loanReceivableAccount && $interestIncomeAccount) {
            $this->createDoubleEntry(
                $loanReceivableAccount->id,
                $interestIncomeAccount->id,
                $interestAmount,
                "Interest accrual - {$loan->loan_number}",
                $loan->id,
                'interest_accrual',
                $branchId,
                $userId
            );
        }
    }

    /**
     * Process interest accrual for savings (accrual basis)
     */
    public function processSavingsInterestAccrual($savingsAccount, $interestAmount, $branchId = null, $userId = null)
    {
        $interestExpenseAccount = ChartOfAccount::where('code', '5700')->first(); // Interest Expense (or create new)
        $interestPayableAccount = ChartOfAccount::where('code', '2100')->first(); // Interest Payable

        if ($interestExpenseAccount && $interestPayableAccount) {
            $this->createDoubleEntry(
                $interestExpenseAccount->id,
                $interestPayableAccount->id,
                $interestAmount,
                "Savings interest accrual - {$savingsAccount->account_number}",
                $savingsAccount->id,
                'savings_interest_accrual',
                $branchId,
                $userId
            );
        }
    }

    /**
     * Process expense entry accounting
     */
    public function processExpenseEntry($expenseAccountId, $amount, $description, $referenceId = null, $referenceType = null, $branchId = null, $userId = null, $transactionDate = null)
    {
        $cashAccount = ChartOfAccount::where('code', '1000')->first(); // Cash on Hand

        if ($cashAccount) {
            $this->createDoubleEntry(
                $expenseAccountId,
                $cashAccount->id,
                $amount,
                $description,
                $referenceId,
                $referenceType,
                $branchId,
                $userId,
                $transactionDate
            );
        }
    }

    /**
     * Process loan loss provision
     */
    public function processLoanLossProvision($amount, $branchId = null, $userId = null)
    {
        $loanLossExpenseAccount = ChartOfAccount::where('code', '5700')->first(); // Loan Loss Expense
        $allowanceForLoanLossAccount = ChartOfAccount::where('code', '1201')->first(); // Allowance for Loan Losses

        if ($loanLossExpenseAccount && $allowanceForLoanLossAccount) {
            $this->createDoubleEntry(
                $loanLossExpenseAccount->id,
                $allowanceForLoanLossAccount->id,
                $amount,
                "Loan loss provision",
                null,
                'loan_loss_provision',
                $branchId,
                $userId
            );
        }
    }

    /**
     * Process loan write-off
     */
    public function processLoanWriteOff($loan, $amount, $branchId = null, $userId = null)
    {
        $loanLossExpenseAccount = ChartOfAccount::where('code', '5700')->first(); // Loan Loss Expense
        $loanReceivableAccount = ChartOfAccount::where('code', '1200')->first(); // Loan Portfolio

        if ($loanLossExpenseAccount && $loanReceivableAccount) {
            $this->createDoubleEntry(
                $loanLossExpenseAccount->id,
                $loanReceivableAccount->id,
                $amount,
                "Loan write-off - {$loan->loan_number}",
                $loan->id,
                'loan_writeoff',
                $branchId,
                $userId
            );
        }
    }

    /**
     * Get account balance
     */
    public function getAccountBalance($accountId, $asOfDate = null)
    {
        return GeneralLedgerEntry::getBalanceForAccount($accountId, $asOfDate);
    }

    /**
     * Get trial balance
     */
    public function getTrialBalance($asOfDate = null)
    {
        return GeneralLedgerEntry::getTrialBalance($asOfDate);
    }

    /**
     * Validate double-entry balance
     */
    public function validateDoubleEntryBalance($entries)
    {
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($entries as $entry) {
            $totalDebits += $entry['debit'] ?? 0;
            $totalCredits += $entry['credit'] ?? 0;
        }

        return abs($totalDebits - $totalCredits) < 0.01; // Allow for minor rounding differences
    }
    /**
     * Get Profit & Loss Statement
     */
    public function getProfitAndLoss($fromDate = null, $toDate = null)
    {
        $fromDate = $fromDate ?? now()->startOfMonth()->toDateString();
        $toDate = $toDate ?? now()->toDateString();

        // Get revenue accounts
        $revenueAccounts = ChartOfAccount::where('type', 'revenue')
            ->where('is_active', true)
            ->get();

        $revenues = [];
        $totalRevenue = 0;

        foreach ($revenueAccounts as $account) {
            $balance = $this->getAccountBalanceForPeriod($account->id, $fromDate, $toDate);
            if ($balance != 0) {
                $revenues[] = [
                    'account' => $account,
                    'balance' => $balance,
                ];
                $totalRevenue += $balance;
            }
        }

        // Get expense accounts
        $expenseAccounts = ChartOfAccount::where('type', 'expense')
            ->where('is_active', true)
            ->get();

        $expenses = [];
        $totalExpenses = 0;

        foreach ($expenseAccounts as $account) {
            $balance = $this->getAccountBalanceForPeriod($account->id, $fromDate, $toDate);
            if ($balance != 0) {
                $expenses[] = [
                    'account' => $account,
                    'balance' => $balance,
                ];
                $totalExpenses += $balance;
            }
        }

        $netIncome = $totalRevenue - $totalExpenses;

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'revenues' => $revenues,
            'total_revenue' => $totalRevenue,
            'expenses' => $expenses,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
        ];
    }

    /**
     * Get Balance Sheet
     */
    public function getBalanceSheet($asOfDate = null)
    {
        $asOfDate = $asOfDate ?? now()->toDateString();

        // Assets
        $assetAccounts = ChartOfAccount::where('type', 'asset')
            ->where('is_active', true)
            ->get();

        $assets = [];
        $totalAssets = 0;

        foreach ($assetAccounts as $account) {
            $balance = $this->getAccountBalance($account->id, $asOfDate);
            if ($balance != 0) {
                $assets[] = [
                    'account' => $account,
                    'balance' => $balance,
                ];
                $totalAssets += $balance;
            }
        }

        // Liabilities
        $liabilityAccounts = ChartOfAccount::where('type', 'liability')
            ->where('is_active', true)
            ->get();

        $liabilities = [];
        $totalLiabilities = 0;

        foreach ($liabilityAccounts as $account) {
            $balance = $this->getAccountBalance($account->id, $asOfDate);
            if ($balance != 0) {
                $liabilities[] = [
                    'account' => $account,
                    'balance' => $balance,
                ];
                $totalLiabilities += $balance;
            }
        }

        // Equity
        $equityAccounts = ChartOfAccount::whereIn('type', ['equity', 'owners_equity'])
            ->where('is_active', true)
            ->get();

        $equity = [];
        $totalEquity = 0;

        foreach ($equityAccounts as $account) {
            $balance = $this->getAccountBalance($account->id, $asOfDate);
            if ($balance != 0) {
                $equity[] = [
                    'account' => $account,
                    'balance' => $balance,
                ];
                $totalEquity += $balance;
            }
        }

        // Add net income to equity
        $netIncome = $this->getProfitAndLoss(now()->startOfYear()->toDateString(), $asOfDate)['net_income'];
        $totalEquity += $netIncome;

        return [
            'as_of_date' => $asOfDate,
            'assets' => $assets,
            'total_assets' => $totalAssets,
            'liabilities' => $liabilities,
            'total_liabilities' => $totalLiabilities,
            'equity' => $equity,
            'net_income' => $netIncome,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilities + $totalEquity,
        ];
    }

    /**
     * Get Cash Flow Statement
     */
    public function getCashFlowStatement($fromDate = null, $toDate = null)
    {
        $fromDate = $fromDate ?? now()->startOfMonth()->toDateString();
        $toDate = $toDate ?? now()->toDateString();

        // Operating Activities
        $netIncome = $this->getProfitAndLoss($fromDate, $toDate)['net_income'];
        
        // Get cash accounts
        $cashAccounts = ChartOfAccount::whereIn('category', ['cash_on_hand', 'cash_in_bank'])
            ->where('is_active', true)
            ->get();

        $operatingActivities = [
            'net_income' => $netIncome,
            'adjustments' => [],
            'total' => $netIncome,
        ];

        // Investing Activities (changes in fixed assets)
        $investingActivities = [];
        $investingTotal = 0;

        // Financing Activities (changes in loans, equity)
        $financingActivities = [];
        $financingTotal = 0;

        $netCashFlow = $operatingActivities['total'] + $investingTotal + $financingTotal;

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'operating_activities' => $operatingActivities,
            'investing_activities' => $investingActivities,
            'financing_activities' => $financingActivities,
            'investing_total' => $investingTotal,
            'financing_total' => $financingTotal,
            'net_cash_flow' => $netCashFlow,
        ];
    }

    /**
     * Get account balance for a specific period
     */
    private function getAccountBalanceForPeriod($accountId, $fromDate, $toDate)
    {
        $account = ChartOfAccount::find($accountId);
        if (!$account) {
            return 0;
        }

        $debits = GeneralLedgerEntry::where('account_id', $accountId)
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->where('status', 'approved')
            ->sum('debit');

        $credits = GeneralLedgerEntry::where('account_id', $accountId)
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->where('status', 'approved')
            ->sum('credit');

        if ($account->normal_balance === 'credit') {
            return $credits - $debits;
        } else {
            return $debits - $credits;
        }
    }

    /**
     * Get revenue breakdown by type
     */
    public function getRevenueBreakdown($fromDate = null, $toDate = null)
    {
        $fromDate = $fromDate ?? now()->startOfMonth()->toDateString();
        $toDate = $toDate ?? now()->toDateString();

        $breakdown = [
            'interest_received' => 0,
            'default_charges' => 0,
            'processing_fee' => 0,
            'system_charge' => 0,
            'other' => 0,
        ];

        $revenueEntries = \App\Models\RevenueEntry::whereBetween('transaction_date', [$fromDate, $toDate])
            ->where('status', 'posted')
            ->get();

        foreach ($revenueEntries as $entry) {
            $breakdown[$entry->revenue_type] += $entry->amount;
        }

        return $breakdown;
    }

    /**
     * Get monthly trends for charts
     */
    public function getMonthlyTrends($months = 12)
    {
        $trends = [];
        $startDate = now()->subMonths($months)->startOfMonth();

        for ($i = 0; $i < $months; $i++) {
            $monthStart = $startDate->copy()->addMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $pl = $this->getProfitAndLoss($monthStart->toDateString(), $monthEnd->toDateString());

            $trends[] = [
                'month' => $monthStart->format('M Y'),
                'revenue' => $pl['total_revenue'],
                'expenses' => $pl['total_expenses'],
                'net_income' => $pl['net_income'],
            ];
        }

        return $trends;
    }

    /**
     * Get cash position
     */
    public function getCashPosition($asOfDate = null)
    {
        $asOfDate = $asOfDate ?? now()->toDateString();

        $cashAccounts = ChartOfAccount::whereIn('category', ['cash_on_hand', 'cash_in_bank'])
            ->where('is_active', true)
            ->get();

        $cashBalance = 0;
        $accounts = [];

        foreach ($cashAccounts as $account) {
            $balance = $this->getAccountBalance($account->id, $asOfDate);
            $cashBalance += $balance;
            $accounts[] = [
                'account' => $account,
                'balance' => $balance,
            ];
        }

        return [
            'total_cash' => $cashBalance,
            'accounts' => $accounts,
        ];
    }
}