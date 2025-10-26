<?php

namespace App\Observers;

use App\Models\Expense;
use App\Events\ExpenseCreated;
use App\Events\ExpenseUpdated;
use App\Events\ExpensePosted;

class ExpenseObserver
{
    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void
    {
        // Broadcast event for real-time updates
        broadcast(new ExpenseCreated($expense))->toOthers();
    }

    /**
     * Handle the Expense "updated" event.
     */
    public function updated(Expense $expense): void
    {
        // If expense was posted, update account balances
        if ($expense->wasChanged('status') && $expense->status === 'posted') {
            $this->updateAccountBalances($expense);
            broadcast(new ExpensePosted($expense))->toOthers();
        }
        
        broadcast(new ExpenseUpdated($expense))->toOthers();
    }

    /**
     * Handle the Expense "deleted" event.
     */
    public function deleted(Expense $expense): void
    {
        // Only allow deletion if not posted
        if ($expense->status === 'posted') {
            throw new \Exception('Cannot delete posted expense');
        }
    }

    /**
     * Update account balances
     */
    private function updateAccountBalances(Expense $expense): void
    {
        // Update expense account
        if ($expense->account) {
            $expense->account->current_balance = $expense->account->getCurrentBalance();
            $expense->account->last_transaction_date = $expense->transaction_date;
            $expense->account->save();
        }

        // Update bank/cash account
        if ($expense->payment_method === 'cash') {
            $cashAccount = \App\Models\ChartOfAccount::where('code', '1000')->first();
            if ($cashAccount) {
                $cashAccount->current_balance = $cashAccount->getCurrentBalance();
                $cashAccount->last_transaction_date = $expense->transaction_date;
                $cashAccount->save();
            }
        } elseif ($expense->bank && $expense->bank->account) {
            $expense->bank->account->current_balance = $expense->bank->account->getCurrentBalance();
            $expense->bank->account->last_transaction_date = $expense->transaction_date;
            $expense->bank->account->save();
            $expense->bank->updateBalance();
        }
    }
}

