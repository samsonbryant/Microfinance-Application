<?php

namespace App\Services;

use App\Models\Reconciliation;
use App\Models\ReconciliationItem;
use App\Models\GeneralLedgerEntry;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;

class ReconciliationService
{
    /**
     * Create a new reconciliation
     */
    public function createReconciliation($type, $accountId, $branchId, $userId, $reconciliationDate, $actualBalance, $notes = null)
    {
        $account = ChartOfAccount::findOrFail($accountId);
        $systemBalance = GeneralLedgerEntry::getBalanceForAccount($accountId, $reconciliationDate);

        $reconciliation = Reconciliation::create([
            'reconciliation_number' => Reconciliation::generateReconciliationNumber(),
            'type' => $type,
            'account_id' => $accountId,
            'branch_id' => $branchId,
            'user_id' => $userId,
            'reconciliation_date' => $reconciliationDate,
            'system_balance' => $systemBalance,
            'actual_balance' => $actualBalance,
            'variance' => $actualBalance - $systemBalance,
            'status' => 'draft',
            'notes' => $notes,
        ]);

        return $reconciliation;
    }

    /**
     * Start reconciliation process by loading system transactions
     */
    public function startReconciliation($reconciliationId)
    {
        $reconciliation = Reconciliation::findOrFail($reconciliationId);
        
        DB::transaction(function () use ($reconciliation) {
            // Load system transactions for the account
            $entries = GeneralLedgerEntry::where('account_id', $reconciliation->account_id)
                ->where('status', 'approved')
                ->where('transaction_date', '<=', $reconciliation->reconciliation_date)
                ->orderBy('transaction_date')
                ->get();

            foreach ($entries as $entry) {
                ReconciliationItem::createFromGeneralLedgerEntry($reconciliation->id, $entry);
            }

            $reconciliation->update(['status' => 'in_progress']);
        });

        return $reconciliation;
    }

    /**
     * Import bank statement data
     */
    public function importBankStatement($reconciliationId, $statementData)
    {
        $reconciliation = Reconciliation::findOrFail($reconciliationId);

        DB::transaction(function () use ($reconciliation, $statementData) {
            foreach ($statementData as $item) {
                ReconciliationItem::createFromBankStatement($reconciliation->id, $item);
            }
        });

        return $reconciliation;
    }

    /**
     * Auto-match reconciliation items
     */
    public function autoMatchItems($reconciliationId)
    {
        $reconciliation = Reconciliation::findOrFail($reconciliationId);
        $items = $reconciliation->items()->where('status', 'unmatched')->get();

        $matched = 0;
        $tolerance = 0.01; // $0.01 tolerance for matching

        foreach ($items as $item) {
            // Find potential matches based on amount and date
            $potentialMatches = $items->where('id', '!=', $item->id)
                ->where('status', 'unmatched')
                ->filter(function ($otherItem) use ($item, $tolerance) {
                    return abs($otherItem->amount - $item->amount) <= $tolerance &&
                           abs($otherItem->transaction_date->diffInDays($item->transaction_date)) <= 1;
                });

            if ($potentialMatches->count() > 0) {
                $match = $potentialMatches->first();
                $item->match();
                $match->match();
                $matched++;
            }
        }

        return $matched;
    }

    /**
     * Complete reconciliation
     */
    public function completeReconciliation($reconciliationId)
    {
        $reconciliation = Reconciliation::findOrFail($reconciliationId);
        
        $unmatchedItems = $reconciliation->getUnmatchedItems();
        
        if ($unmatchedItems->count() > 0) {
            throw new \Exception('Cannot complete reconciliation with unmatched items. Please resolve all unmatched items first.');
        }

        $reconciliation->update(['status' => 'completed']);
        
        return $reconciliation;
    }

    /**
     * Create adjustment entries for variances
     */
    public function createAdjustmentEntries($reconciliationId, $adjustmentData)
    {
        $reconciliation = Reconciliation::findOrFail($reconciliationId);
        
        if (!$reconciliation->isCompleted()) {
            throw new \Exception('Reconciliation must be completed before creating adjustment entries.');
        }

        DB::transaction(function () use ($reconciliation, $adjustmentData) {
            foreach ($adjustmentData as $adjustment) {
                // Create journal entry for adjustment
                $journalEntry = \App\Models\JournalEntry::create([
                    'journal_number' => \App\Models\JournalEntry::generateJournalNumber(),
                    'branch_id' => $reconciliation->branch_id,
                    'user_id' => auth()->id(),
                    'transaction_date' => $reconciliation->reconciliation_date,
                    'description' => "Reconciliation adjustment - {$reconciliation->reconciliation_number}",
                    'reference_number' => $reconciliation->reconciliation_number,
                    'total_debits' => abs($adjustment['amount']),
                    'total_credits' => abs($adjustment['amount']),
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                // Create journal entry lines
                if ($adjustment['amount'] > 0) {
                    // Debit the account being reconciled
                    $journalEntry->lines()->create([
                        'account_id' => $reconciliation->account_id,
                        'debit' => $adjustment['amount'],
                        'credit' => 0,
                        'description' => $adjustment['description'],
                    ]);

                    // Credit adjustment account
                    $journalEntry->lines()->create([
                        'account_id' => $adjustment['adjustment_account_id'],
                        'debit' => 0,
                        'credit' => $adjustment['amount'],
                        'description' => $adjustment['description'],
                    ]);
                } else {
                    // Credit the account being reconciled
                    $journalEntry->lines()->create([
                        'account_id' => $reconciliation->account_id,
                        'debit' => 0,
                        'credit' => abs($adjustment['amount']),
                        'description' => $adjustment['description'],
                    ]);

                    // Debit adjustment account
                    $journalEntry->lines()->create([
                        'account_id' => $adjustment['adjustment_account_id'],
                        'debit' => abs($adjustment['amount']),
                        'credit' => 0,
                        'description' => $adjustment['description'],
                    ]);
                }

                // Post the journal entry
                $journalEntry->post();
            }
        });

        return $reconciliation;
    }

    /**
     * Get reconciliation summary
     */
    public function getReconciliationSummary($reconciliationId)
    {
        $reconciliation = Reconciliation::with('items')->findOrFail($reconciliationId);
        
        $summary = [
            'total_items' => $reconciliation->items->count(),
            'matched_items' => $reconciliation->getMatchedItems()->count(),
            'unmatched_items' => $reconciliation->getUnmatchedItems()->count(),
            'disputed_items' => $reconciliation->getDisputedItems()->count(),
            'total_matched_amount' => $reconciliation->getMatchedItems()->sum('amount'),
            'total_unmatched_amount' => $reconciliation->getUnmatchedItems()->sum('amount'),
            'total_disputed_amount' => $reconciliation->getDisputedItems()->sum('amount'),
            'variance' => $reconciliation->variance,
            'is_balanced' => $reconciliation->isBalanced(),
        ];

        return $summary;
    }

    /**
     * Get reconciliation history for an account
     */
    public function getReconciliationHistory($accountId, $limit = 10)
    {
        return Reconciliation::where('account_id', $accountId)
            ->with(['user', 'approvedBy'])
            ->orderBy('reconciliation_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get overdue reconciliations
     */
    public function getOverdueReconciliations($daysOverdue = 30)
    {
        $cutoffDate = now()->subDays($daysOverdue)->toDateString();
        
        return Reconciliation::where('status', '!=', 'approved')
            ->where('reconciliation_date', '<', $cutoffDate)
            ->with(['account', 'branch', 'user'])
            ->get();
    }

    /**
     * Generate reconciliation report
     */
    public function generateReconciliationReport($reconciliationId)
    {
        $reconciliation = Reconciliation::with(['account', 'branch', 'user', 'items'])->findOrFail($reconciliationId);
        
        $report = [
            'reconciliation' => $reconciliation,
            'summary' => $this->getReconciliationSummary($reconciliationId),
            'matched_items' => $reconciliation->getMatchedItems(),
            'unmatched_items' => $reconciliation->getUnmatchedItems(),
            'disputed_items' => $reconciliation->getDisputedItems(),
        ];

        return $report;
    }
}
