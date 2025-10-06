<?php

namespace App\Services;

use App\Models\GeneralLedger;
use App\Models\Transaction;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Create double-entry accounting entries
     */
    public function createDoubleEntry($debitAccount, $creditAccount, $amount, $description, $referenceId = null, $referenceType = null)
    {
        DB::transaction(function () use ($debitAccount, $creditAccount, $amount, $description, $referenceId, $referenceType) {
            // Debit entry
            GeneralLedger::create([
                'account_id' => $debitAccount,
                'debit' => $amount,
                'credit' => 0,
                'description' => $description,
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
                'transaction_date' => now(),
            ]);

            // Credit entry
            GeneralLedger::create([
                'account_id' => $creditAccount,
                'debit' => 0,
                'credit' => $amount,
                'description' => $description,
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
                'transaction_date' => now(),
            ]);
        });
    }

    /**
     * Process loan disbursement accounting
     */
    public function processLoanDisbursement($loan, $amount)
    {
        $loanReceivableAccount = ChartOfAccount::where('code', '1200')->first(); // Loan Receivable
        $cashAccount = ChartOfAccount::where('code', '1000')->first(); // Cash

        if ($loanReceivableAccount && $cashAccount) {
            $this->createDoubleEntry(
                $loanReceivableAccount->id,
                $cashAccount->id,
                $amount,
                "Loan disbursement - {$loan->loan_number}",
                $loan->id,
                'App\\Models\\Loan'
            );
        }
    }

    /**
     * Process loan repayment accounting
     */
    public function processLoanRepayment($loan, $amount, $principalAmount, $interestAmount)
    {
        $cashAccount = ChartOfAccount::where('code', '1000')->first(); // Cash
        $loanReceivableAccount = ChartOfAccount::where('code', '1200')->first(); // Loan Receivable
        $interestIncomeAccount = ChartOfAccount::where('code', '4000')->first(); // Interest Income

        if ($cashAccount && $loanReceivableAccount && $interestIncomeAccount) {
            // Credit cash
            GeneralLedger::create([
                'account_id' => $cashAccount->id,
                'debit' => 0,
                'credit' => $amount,
                'description' => "Loan repayment - {$loan->loan_number}",
                'reference_id' => $loan->id,
                'reference_type' => 'App\\Models\\Loan',
                'transaction_date' => now(),
            ]);

            // Debit loan receivable (principal)
            GeneralLedger::create([
                'account_id' => $loanReceivableAccount->id,
                'debit' => $principalAmount,
                'credit' => 0,
                'description' => "Principal repayment - {$loan->loan_number}",
                'reference_id' => $loan->id,
                'reference_type' => 'App\\Models\\Loan',
                'transaction_date' => now(),
            ]);

            // Debit interest income (interest)
            GeneralLedger::create([
                'account_id' => $interestIncomeAccount->id,
                'debit' => $interestAmount,
                'credit' => 0,
                'description' => "Interest income - {$loan->loan_number}",
                'reference_id' => $loan->id,
                'reference_type' => 'App\\Models\\Loan',
                'transaction_date' => now(),
            ]);
        }
    }

    /**
     * Process savings deposit accounting
     */
    public function processSavingsDeposit($savingsAccount, $amount)
    {
        $cashAccount = ChartOfAccount::where('code', '1000')->first(); // Cash
        $savingsLiabilityAccount = ChartOfAccount::where('code', '2000')->first(); // Savings Liability

        if ($cashAccount && $savingsLiabilityAccount) {
            $this->createDoubleEntry(
                $cashAccount->id,
                $savingsLiabilityAccount->id,
                $amount,
                "Savings deposit - {$savingsAccount->account_number}",
                $savingsAccount->id,
                'App\\Models\\SavingsAccount'
            );
        }
    }

    /**
     * Process savings withdrawal accounting
     */
    public function processSavingsWithdrawal($savingsAccount, $amount)
    {
        $savingsLiabilityAccount = ChartOfAccount::where('code', '2000')->first(); // Savings Liability
        $cashAccount = ChartOfAccount::where('code', '1000')->first(); // Cash

        if ($savingsLiabilityAccount && $cashAccount) {
            $this->createDoubleEntry(
                $savingsLiabilityAccount->id,
                $cashAccount->id,
                $amount,
                "Savings withdrawal - {$savingsAccount->account_number}",
                $savingsAccount->id,
                'App\\Models\\SavingsAccount'
            );
        }
    }

    /**
     * Get account balance
     */
    public function getAccountBalance($accountId)
    {
        $debits = GeneralLedger::where('account_id', $accountId)->sum('debit');
        $credits = GeneralLedger::where('account_id', $accountId)->sum('credit');
        
        return $debits - $credits;
    }

    /**
     * Get trial balance
     */
    public function getTrialBalance()
    {
        return ChartOfAccount::with(['generalLedgers' => function($query) {
            $query->selectRaw('account_id, SUM(debit) as total_debits, SUM(credit) as total_credits')
                  ->groupBy('account_id');
        }])->get()->map(function($account) {
            $debits = $account->generalLedgers->sum('total_debits');
            $credits = $account->generalLedgers->sum('total_credits');
            $balance = $debits - $credits;
            
            return [
                'account_code' => $account->code,
                'account_name' => $account->name,
                'debits' => $debits,
                'credits' => $credits,
                'balance' => $balance,
            ];
        });
    }

    /**
     * Get profit and loss statement
     */
    public function getProfitAndLoss($startDate, $endDate)
    {
        $revenue = ChartOfAccount::where('type', 'revenue')
            ->with(['generalLedgers' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('transaction_date', [$startDate, $endDate]);
            }])
            ->get()
            ->sum(function($account) {
                return $account->generalLedgers->sum('credit') - $account->generalLedgers->sum('debit');
            });

        $expenses = ChartOfAccount::where('type', 'expense')
            ->with(['generalLedgers' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('transaction_date', [$startDate, $endDate]);
            }])
            ->get()
            ->sum(function($account) {
                return $account->generalLedgers->sum('debit') - $account->generalLedgers->sum('credit');
            });

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'net_profit' => $revenue - $expenses,
        ];
    }

    /**
     * Get balance sheet
     */
    public function getBalanceSheet()
    {
        $assets = ChartOfAccount::where('type', 'asset')
            ->with('generalLedgers')
            ->get()
            ->sum(function($account) {
                return $account->generalLedgers->sum('debit') - $account->generalLedgers->sum('credit');
            });

        $liabilities = ChartOfAccount::where('type', 'liability')
            ->with('generalLedgers')
            ->get()
            ->sum(function($account) {
                return $account->generalLedgers->sum('credit') - $account->generalLedgers->sum('debit');
            });

        $equity = ChartOfAccount::where('type', 'equity')
            ->with('generalLedgers')
            ->get()
            ->sum(function($account) {
                return $account->generalLedgers->sum('credit') - $account->generalLedgers->sum('debit');
            });

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_liabilities_equity' => $liabilities + $equity,
        ];
    }
}
