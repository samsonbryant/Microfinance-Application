<?php

namespace App\Observers;

use App\Models\JournalEntry;
use App\Events\JournalEntryCreated;
use App\Events\JournalEntryUpdated;
use App\Events\JournalEntryPosted;

class JournalEntryObserver
{
    /**
     * Handle the JournalEntry "created" event.
     */
    public function created(JournalEntry $journalEntry): void
    {
        // Broadcast event for real-time updates
        broadcast(new JournalEntryCreated($journalEntry))->toOthers();
    }

    /**
     * Handle the JournalEntry "updated" event.
     */
    public function updated(JournalEntry $journalEntry): void
    {
        // If journal entry was posted, update account balances
        if ($journalEntry->wasChanged('status') && $journalEntry->status === 'posted') {
            $this->updateAccountBalances($journalEntry);
            broadcast(new JournalEntryPosted($journalEntry))->toOthers();
        }
        
        broadcast(new JournalEntryUpdated($journalEntry))->toOthers();
    }

    /**
     * Handle the JournalEntry "deleted" event.
     */
    public function deleted(JournalEntry $journalEntry): void
    {
        // Only allow deletion if not posted
        if ($journalEntry->status === 'posted') {
            throw new \Exception('Cannot delete posted journal entry');
        }
    }

    /**
     * Update account balances for all affected accounts
     */
    private function updateAccountBalances(JournalEntry $journalEntry): void
    {
        foreach ($journalEntry->lines as $line) {
            if ($line->account) {
                $line->account->current_balance = $line->account->getCurrentBalance();
                $line->account->last_transaction_date = $journalEntry->transaction_date;
                $line->account->save();
            }
        }
    }
}

