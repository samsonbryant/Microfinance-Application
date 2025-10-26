<?php

namespace App\Services;

use App\Models\GeneralLedgerEntry;
use App\Models\JournalEntry;
use App\Models\ExpenseEntry;
use App\Models\Reconciliation;
use App\Models\Loan;
use App\Models\SavingsAccount;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RealtimeActivityService
{
    /**
     * Get real-time activities across all users
     */
    public function getAllUserActivities($limit = 50)
    {
        $cacheKey = "all_user_activities_" . now()->format('Y-m-d-H-i');
        
        return Cache::remember($cacheKey, 60, function () use ($limit) {
            $activities = \Spatie\Activitylog\Models\Activity::with(['causer', 'subject'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'user' => $activity->causer->name ?? 'System',
                    'user_id' => $activity->causer_id,
                    'user_avatar' => $activity->causer->avatar ?? null,
                    'subject_type' => class_basename($activity->subject_type ?? ''),
                    'subject_id' => $activity->subject_id,
                    'created_at' => $activity->created_at,
                    'log_name' => $activity->log_name,
                    'properties' => $activity->properties,
                    'branch_id' => $this->getBranchIdFromActivity($activity),
                ];
            });
        });
    }

    /**
     * Get activities for a specific user
     */
    public function getUserActivities($userId, $limit = 20)
    {
        $cacheKey = "user_activities_{$userId}_" . now()->format('Y-m-d-H-i');
        
        return Cache::remember($cacheKey, 60, function () use ($userId, $limit) {
            $activities = \Spatie\Activitylog\Models\Activity::where('causer_id', $userId)
                ->with(['causer', 'subject'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'user' => $activity->causer->name ?? 'System',
                    'user_id' => $activity->causer_id,
                    'subject_type' => class_basename($activity->subject_type ?? ''),
                    'subject_id' => $activity->subject_id,
                    'created_at' => $activity->created_at,
                    'log_name' => $activity->log_name,
                    'properties' => $activity->properties,
                ];
            });
        });
    }

    /**
     * Get activities for a specific branch
     */
    public function getBranchActivities($branchId, $limit = 30)
    {
        $cacheKey = "branch_activities_{$branchId}_" . now()->format('Y-m-d-H-i');
        
        return Cache::remember($cacheKey, 60, function () use ($branchId, $limit) {
            $activities = \Spatie\Activitylog\Models\Activity::whereHas('subject', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->orWhereHas('causer', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->with(['causer', 'subject'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

            return $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'user' => $activity->causer->name ?? 'System',
                    'user_id' => $activity->causer_id,
                    'subject_type' => class_basename($activity->subject_type ?? ''),
                    'subject_id' => $activity->subject_id,
                    'created_at' => $activity->created_at,
                    'log_name' => $activity->log_name,
                    'properties' => $activity->properties,
                ];
            });
        });
    }

    /**
     * Get recent financial activities
     */
    public function getFinancialActivities($limit = 20)
    {
        $cacheKey = "financial_activities_" . now()->format('Y-m-d-H-i');
        
        return Cache::remember($cacheKey, 60, function () use ($limit) {
            $financialLogNames = [
                'general_ledger_entry',
                'journal_entry',
                'expense_entry',
                'reconciliation',
                'chart_of_account',
            ];

            $activities = \Spatie\Activitylog\Models\Activity::whereIn('log_name', $financialLogNames)
                ->with(['causer', 'subject'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'user' => $activity->causer->name ?? 'System',
                    'user_id' => $activity->causer_id,
                    'subject_type' => class_basename($activity->subject_type ?? ''),
                    'subject_id' => $activity->subject_id,
                    'created_at' => $activity->created_at,
                    'log_name' => $activity->log_name,
                    'properties' => $activity->properties,
                    'amount' => $this->extractAmountFromActivity($activity),
                ];
            });
        });
    }

    /**
     * Get system-wide activity statistics
     */
    public function getActivityStatistics($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: now()->startOfDay();
        $endDate = $endDate ?: now()->endOfDay();

        $cacheKey = "activity_stats_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";
        
        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            $activities = \Spatie\Activitylog\Models\Activity::whereBetween('created_at', [$startDate, $endDate])
                ->get();

            return [
                'total_activities' => $activities->count(),
                'unique_users' => $activities->pluck('causer_id')->unique()->count(),
                'activities_by_type' => $activities->groupBy('log_name')->map->count(),
                'activities_by_hour' => $activities->groupBy(function ($activity) {
                    return $activity->created_at->format('H');
                })->map->count(),
                'top_users' => $activities->groupBy('causer_id')->map->count()->sortDesc()->take(5),
                'most_active_hour' => $activities->groupBy(function ($activity) {
                    return $activity->created_at->format('H');
                })->map->count()->sortDesc()->keys()->first(),
            ];
        });
    }

    /**
     * Get real-time notifications for pending approvals
     */
    public function getPendingApprovalNotifications()
    {
        $notifications = [];

        // Pending journal entries
        $pendingJournals = JournalEntry::where('status', 'pending')->count();
        if ($pendingJournals > 0) {
            $notifications[] = [
                'type' => 'journal_entry',
                'title' => 'Pending Journal Entries',
                'message' => "{$pendingJournals} journal entries awaiting approval",
                'count' => $pendingJournals,
                'priority' => 'medium',
                'url' => route('accounting.journal-entries'),
            ];
        }

        // Pending expense entries
        $pendingExpenses = ExpenseEntry::where('status', 'pending')->count();
        if ($pendingExpenses > 0) {
            $notifications[] = [
                'type' => 'expense_entry',
                'title' => 'Pending Expense Entries',
                'message' => "{$pendingExpenses} expense entries awaiting approval",
                'count' => $pendingExpenses,
                'priority' => 'high',
                'url' => route('accounting.expense-entries'),
            ];
        }

        // Completed reconciliations awaiting approval
        $pendingReconciliations = Reconciliation::where('status', 'completed')->count();
        if ($pendingReconciliations > 0) {
            $notifications[] = [
                'type' => 'reconciliation',
                'title' => 'Pending Reconciliations',
                'message' => "{$pendingReconciliations} reconciliations awaiting approval",
                'count' => $pendingReconciliations,
                'priority' => 'high',
                'url' => route('accounting.reconciliations'),
            ];
        }

        return $notifications;
    }

    /**
     * Get system health indicators
     */
    public function getSystemHealthIndicators()
    {
        $indicators = [];

        // Check for unbalanced journal entries
        $unbalancedJournals = JournalEntry::where('status', '!=', 'approved')
            ->whereRaw('ABS(total_debits - total_credits) > 0.01')
            ->count();

        if ($unbalancedJournals > 0) {
            $indicators[] = [
                'type' => 'error',
                'title' => 'Unbalanced Journal Entries',
                'message' => "{$unbalancedJournals} journal entries are not balanced",
                'count' => $unbalancedJournals,
            ];
        }

        // Check for overdue reconciliations
        $overdueReconciliations = Reconciliation::where('status', '!=', 'approved')
            ->where('reconciliation_date', '<', now()->subDays(30))
            ->count();

        if ($overdueReconciliations > 0) {
            $indicators[] = [
                'type' => 'warning',
                'title' => 'Overdue Reconciliations',
                'message' => "{$overdueReconciliations} reconciliations are overdue",
                'count' => $overdueReconciliations,
            ];
        }

        // Check for large variances
        $largeVariances = Reconciliation::where('status', '!=', 'approved')
            ->whereRaw('ABS(variance) > 1000')
            ->count();

        if ($largeVariances > 0) {
            $indicators[] = [
                'type' => 'warning',
                'title' => 'Large Variances',
                'message' => "{$largeVariances} reconciliations have large variances",
                'count' => $largeVariances,
            ];
        }

        return $indicators;
    }

    /**
     * Get real-time user presence
     */
    public function getActiveUsers()
    {
        $cacheKey = "active_users_" . now()->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 300, function () {
            $activeUsers = \Spatie\Activitylog\Models\Activity::where('created_at', '>=', now()->subMinutes(15))
                ->whereNotNull('causer_id')
                ->with('causer')
                ->get()
                ->groupBy('causer_id')
                ->map(function ($activities, $userId) {
                    $user = $activities->first()->causer;
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar ?? null,
                        'last_activity' => $activities->max('created_at'),
                        'activity_count' => $activities->count(),
                        'current_activity' => $activities->first()->description,
                    ];
                })
                ->sortByDesc('last_activity')
                ->values();

            return $activeUsers;
        });
    }

    /**
     * Get branch activity summary
     */
    public function getBranchActivitySummary()
    {
        $cacheKey = "branch_activity_summary_" . now()->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 300, function () {
            $branches = \App\Models\Branch::with('users')->get();
            
            return $branches->map(function ($branch) {
                $activities = \Spatie\Activitylog\Models\Activity::whereHas('causer', function ($query) use ($branch) {
                    $query->where('branch_id', $branch->id);
                })
                ->where('created_at', '>=', now()->subHours(24))
                ->get();

                return [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'total_activities' => $activities->count(),
                    'unique_users' => $activities->pluck('causer_id')->unique()->count(),
                    'last_activity' => $activities->max('created_at'),
                    'activities_by_type' => $activities->groupBy('log_name')->map->count(),
                ];
            });
        });
    }

    /**
     * Extract branch ID from activity
     */
    private function getBranchIdFromActivity($activity)
    {
        if ($activity->subject && method_exists($activity->subject, 'branch_id')) {
            return $activity->subject->branch_id;
        }

        if ($activity->causer && $activity->causer->branch_id) {
            return $activity->causer->branch_id;
        }

        return null;
    }

    /**
     * Extract amount from activity properties
     */
    private function extractAmountFromActivity($activity)
    {
        if (!$activity->properties) {
            return 0;
        }

        $properties = $activity->properties->toArray();

        // Try to extract amount from various property keys
        $amountKeys = ['amount', 'debit', 'credit', 'total_debits', 'total_credits', 'variance'];
        
        foreach ($amountKeys as $key) {
            if (isset($properties[$key]) && is_numeric($properties[$key])) {
                return (float) $properties[$key];
            }
        }

        return 0;
    }

    /**
     * Clear activity cache
     */
    public function clearActivityCache()
    {
        $patterns = [
            'all_user_activities_*',
            'user_activities_*',
            'branch_activities_*',
            'financial_activities_*',
            'activity_stats_*',
            'active_users_*',
            'branch_activity_summary_*',
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * Get real-time updates since last check
     */
    public function getRealtimeUpdates($lastActivityId = 0)
    {
        $newActivities = \Spatie\Activitylog\Models\Activity::where('id', '>', $lastActivityId)
            ->with(['causer', 'subject'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return [
            'activities' => $newActivities,
            'last_activity_id' => $newActivities->max('id') ?: $lastActivityId,
            'pending_approvals' => $this->getPendingApprovalNotifications(),
            'system_health' => $this->getSystemHealthIndicators(),
            'active_users' => $this->getActiveUsers(),
        ];
    }
}
