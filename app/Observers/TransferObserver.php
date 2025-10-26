<?php

namespace App\Observers;

use App\Models\Transfer;
use App\Events\TransferCreated;
use App\Events\TransferUpdated;
use App\Events\TransferProcessed;

class TransferObserver
{
    /**
     * Handle the Transfer "created" event.
     */
    public function created(Transfer $transfer): void
    {
        // Broadcast event for real-time updates
        broadcast(new TransferCreated($transfer))->toOthers();
    }

    /**
     * Handle the Transfer "updated" event.
     */
    public function updated(Transfer $transfer): void
    {
        // If transfer was posted, update account balances
        if ($transfer->wasChanged('status') && $transfer->status === 'posted') {
            $this->updateAccountBalances($transfer);
            broadcast(new TransferProcessed($transfer))->toOthers();
        }
        
        broadcast(new TransferUpdated($transfer))->toOthers();
    }

    /**
     * Handle the Transfer "deleted" event.
     */
    public function deleted(Transfer $transfer): void
    {
        // Only allow deletion if not posted
        if ($transfer->status === 'posted') {
            throw new \Exception('Cannot delete posted transfer');
        }
    }

    /**
     * Update account balances
     */
    private function updateAccountBalances(Transfer $transfer): void
    {
        // Update from account
        if ($transfer->fromAccount) {
            $transfer->fromAccount->current_balance = $transfer->fromAccount->getCurrentBalance();
            $transfer->fromAccount->last_transaction_date = $transfer->transaction_date;
            $transfer->fromAccount->save();
        }

        // Update to account
        if ($transfer->toAccount) {
            $transfer->toAccount->current_balance = $transfer->toAccount->getCurrentBalance();
            $transfer->toAccount->last_transaction_date = $transfer->transaction_date;
            $transfer->toAccount->save();
        }

        // Update bank balances
        if ($transfer->fromBank) {
            $transfer->fromBank->updateBalance();
        }
        if ($transfer->toBank) {
            $transfer->toBank->updateBalance();
        }
    }
}

