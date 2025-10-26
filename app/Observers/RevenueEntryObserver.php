<?php

namespace App\Observers;

use App\Models\RevenueEntry;
use App\Events\RevenueCreated;
use App\Events\RevenueUpdated;
use App\Events\RevenuePosted;

class RevenueEntryObserver
{
    /**
     * Handle the RevenueEntry "created" event.
     */
    public function created(RevenueEntry $revenueEntry): void
    {
        // Broadcast event for real-time updates
        broadcast(new RevenueCreated($revenueEntry))->toOthers();
    }

    /**
     * Handle the RevenueEntry "updated" event.
     */
    public function updated(RevenueEntry $revenueEntry): void
    {
        // If revenue was posted, update account balances
        if ($revenueEntry->wasChanged('status') && $revenueEntry->status === 'posted') {
            $this->updateAccountBalances($revenueEntry);
            broadcast(new RevenuePosted($revenueEntry))->toOthers();
        }
        
        broadcast(new RevenueUpdated($revenueEntry))->toOthers();
    }

    /**
     * Handle the RevenueEntry "deleted" event.
     */
    public function deleted(RevenueEntry $revenueEntry): void
    {
        // Only allow deletion if not posted
        if ($revenueEntry->status === 'posted') {
            throw new \Exception('Cannot delete posted revenue entry');
        }
    }

    /**
     * Update account balances
     */
    private function updateAccountBalances(RevenueEntry $revenueEntry): void
    {
        // Update revenue account
        if ($revenueEntry->account) {
            $revenueEntry->account->current_balance = $revenueEntry->account->getCurrentBalance();
            $revenueEntry->account->last_transaction_date = $revenueEntry->transaction_date;
            $revenueEntry->account->save();
        }

        // Update bank/cash account
        if ($revenueEntry->bank && $revenueEntry->bank->account) {
            $revenueEntry->bank->account->current_balance = $revenueEntry->bank->account->getCurrentBalance();
            $revenueEntry->bank->account->last_transaction_date = $revenueEntry->transaction_date;
            $revenueEntry->bank->account->save();
            $revenueEntry->bank->updateBalance();
        } else {
            $cashAccount = \App\Models\ChartOfAccount::where('code', '1000')->first();
            if ($cashAccount) {
                $cashAccount->current_balance = $cashAccount->getCurrentBalance();
                $cashAccount->last_transaction_date = $revenueEntry->transaction_date;
                $cashAccount->save();
            }
        }
    }
}

