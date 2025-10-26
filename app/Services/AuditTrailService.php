<?php

namespace App\Services;

use Spatie\Activitylog\Models\Activity;
use App\Models\GeneralLedgerEntry;
use App\Models\JournalEntry;
use App\Models\ExpenseEntry;
use App\Models\Reconciliation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuditTrailService
{
    /**
     * Log accounting transaction
     */
    public function logAccountingTransaction($action, $model, $description = null, $properties = [])
    {
        activity()
            ->performedOn($model)
            ->causedBy(Auth::user())
            ->withProperties($properties)
            ->log($description ?: $action);
    }

    /**
     * Log general ledger entry
     */
    public function logGeneralLedgerEntry($entry, $action = 'created')
    {
        $properties = [
            'entry_number' => $entry->entry_number,
            'account_id' => $entry->account_id,
            'account_name' => $entry->account->name,
            'debit' => $entry->debit,
            'credit' => $entry->credit,
            'transaction_date' => $entry->transaction_date,
            'reference_type' => $entry->reference_type,
            'reference_id' => $entry->reference_id,
            'status' => $entry->status,
        ];

        $this->logAccountingTransaction($action, $entry, "General Ledger Entry {$action}", $properties);
    }

    /**
     * Log journal entry
     */
    public function logJournalEntry($journalEntry, $action = 'created')
    {
        $properties = [
            'journal_number' => $journalEntry->journal_number,
            'total_debits' => $journalEntry->total_debits,
            'total_credits' => $journalEntry->total_credits,
            'status' => $journalEntry->status,
            'transaction_date' => $journalEntry->transaction_date,
            'lines_count' => $journalEntry->lines->count(),
        ];

        $this->logAccountingTransaction($action, $journalEntry, "Journal Entry {$action}", $properties);
    }

    /**
     * Log expense entry
     */
    public function logExpenseEntry($expenseEntry, $action = 'created')
    {
        $properties = [
            'expense_number' => $expenseEntry->expense_number,
            'account_id' => $expenseEntry->account_id,
            'account_name' => $expenseEntry->account->name,
            'amount' => $expenseEntry->amount,
            'expense_date' => $expenseEntry->expense_date,
            'status' => $expenseEntry->status,
        ];

        $this->logAccountingTransaction($action, $expenseEntry, "Expense Entry {$action}", $properties);
    }

    /**
     * Log reconciliation
     */
    public function logReconciliation($reconciliation, $action = 'created')
    {
        $properties = [
            'reconciliation_number' => $reconciliation->reconciliation_number,
            'type' => $reconciliation->type,
            'account_id' => $reconciliation->account_id,
            'account_name' => $reconciliation->account->name,
            'system_balance' => $reconciliation->system_balance,
            'actual_balance' => $reconciliation->actual_balance,
            'variance' => $reconciliation->variance,
            'status' => $reconciliation->status,
        ];

        $this->logAccountingTransaction($action, $reconciliation, "Reconciliation {$action}", $properties);
    }

    /**
     * Log chart of account changes
     */
    public function logChartOfAccountChange($account, $action = 'created', $oldData = null)
    {
        $properties = [
            'account_code' => $account->code,
            'account_name' => $account->name,
            'account_type' => $account->type,
            'account_category' => $account->category,
            'normal_balance' => $account->normal_balance,
            'is_active' => $account->is_active,
        ];

        if ($oldData) {
            $properties['old_data'] = $oldData;
        }

        $this->logAccountingTransaction($action, $account, "Chart of Account {$action}", $properties);
    }

    /**
     * Get audit trail for a specific model
     */
    public function getModelAuditTrail($model, $limit = 50)
    {
        return Activity::forSubject($model)
            ->with(['causer'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary($userId, $startDate = null, $endDate = null)
    {
        $query = Activity::where('causer_id', $userId);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $activities = $query->get();

        return [
            'total_activities' => $activities->count(),
            'activities_by_type' => $activities->groupBy('log_name'),
            'activities_by_date' => $activities->groupBy(function ($activity) {
                return $activity->created_at->format('Y-m-d');
            }),
            'last_activity' => $activities->max('created_at'),
            'first_activity' => $activities->min('created_at'),
        ];
    }

    /**
     * Get system-wide audit trail
     */
    public function getSystemAuditTrail($startDate = null, $endDate = null, $userId = null, $logName = null)
    {
        $query = Activity::with(['causer', 'subject']);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        if ($userId) {
            $query->where('causer_id', $userId);
        }

        if ($logName) {
            $query->where('log_name', $logName);
        }

        return $query->orderBy('created_at', 'desc')->paginate(100);
    }

    /**
     * Get compliance report
     */
    public function getComplianceReport($startDate, $endDate)
    {
        $activities = Activity::whereBetween('created_at', [$startDate, $endDate])
            ->with(['causer', 'subject'])
            ->get();

        $report = [
            'total_activities' => $activities->count(),
            'unique_users' => $activities->pluck('causer_id')->unique()->count(),
            'activities_by_type' => $activities->groupBy('log_name'),
            'activities_by_user' => $activities->groupBy('causer_id'),
            'activities_by_date' => $activities->groupBy(function ($activity) {
                return $activity->created_at->format('Y-m-d');
            }),
            'critical_activities' => $this->getCriticalActivities($activities),
            'data_integrity_checks' => $this->getDataIntegrityChecks($startDate, $endDate),
        ];

        return $report;
    }

    /**
     * Get critical activities
     */
    private function getCriticalActivities($activities)
    {
        $criticalKeywords = [
            'delete', 'approve', 'reject', 'post', 'reverse', 'adjustment',
            'write-off', 'provision', 'reconciliation', 'audit'
        ];

        return $activities->filter(function ($activity) use ($criticalKeywords) {
            $description = strtolower($activity->description);
            return collect($criticalKeywords)->contains(function ($keyword) use ($description) {
                return str_contains($description, $keyword);
            });
        });
    }

    /**
     * Get data integrity checks
     */
    private function getDataIntegrityChecks($startDate, $endDate)
    {
        $checks = [];

        // Check for unbalanced journal entries
        $unbalancedJournals = JournalEntry::whereBetween('created_at', [$startDate, $endDate])
            ->whereRaw('ABS(total_debits - total_credits) > 0.01')
            ->count();
        $checks['unbalanced_journal_entries'] = $unbalancedJournals;

        // Check for unapproved general ledger entries
        $unapprovedEntries = GeneralLedgerEntry::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'approved')
            ->count();
        $checks['unapproved_ledger_entries'] = $unapprovedEntries;

        // Check for overdue reconciliations
        $overdueReconciliations = Reconciliation::where('status', '!=', 'approved')
            ->where('reconciliation_date', '<', now()->subDays(30))
            ->count();
        $checks['overdue_reconciliations'] = $overdueReconciliations;

        // Check for large variances in reconciliations
        $largeVariances = Reconciliation::whereBetween('created_at', [$startDate, $endDate])
            ->whereRaw('ABS(variance) > 1000')
            ->count();
        $checks['large_reconciliation_variances'] = $largeVariances;

        return $checks;
    }

    /**
     * Get audit trail statistics
     */
    public function getAuditTrailStatistics($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: now()->startOfMonth();
        $endDate = $endDate ?: now()->endOfMonth();

        $activities = Activity::whereBetween('created_at', [$startDate, $endDate])->get();

        return [
            'total_activities' => $activities->count(),
            'unique_users' => $activities->pluck('causer_id')->unique()->count(),
            'activities_by_type' => $activities->groupBy('log_name')->map->count(),
            'activities_by_user' => $activities->groupBy('causer_id')->map->count(),
            'daily_activity' => $activities->groupBy(function ($activity) {
                return $activity->created_at->format('Y-m-d');
            })->map->count(),
            'most_active_user' => $activities->groupBy('causer_id')->map->count()->sortDesc()->first(),
            'most_common_activity' => $activities->groupBy('log_name')->map->count()->sortDesc()->first(),
        ];
    }

    /**
     * Export audit trail
     */
    public function exportAuditTrail($startDate, $endDate, $format = 'csv')
    {
        $activities = Activity::whereBetween('created_at', [$startDate, $endDate])
            ->with(['causer', 'subject'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $activities->map(function ($activity) {
            return [
                'timestamp' => $activity->created_at->format('Y-m-d H:i:s'),
                'user' => $activity->causer->name ?? 'System',
                'action' => $activity->description,
                'model_type' => $activity->subject_type,
                'model_id' => $activity->subject_id,
                'properties' => json_encode($activity->properties),
            ];
        });

        return $data;
    }
}
