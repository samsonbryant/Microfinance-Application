<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\Loan;
use App\Models\RecoveryAction;
use App\Models\CommunicationLog;
use App\Notifications\OverdueLoanNotification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CollectionService
{
    /**
     * Identify overdue loans
     */
    public function identifyOverdueLoans()
    {
        $overdueLoans = Loan::where('status', 'active')
            ->where('due_date', '<', now())
            ->whereDoesntHave('collections', function($query) {
                $query->where('status', 'resolved');
            })
            ->get();

        foreach ($overdueLoans as $loan) {
            $this->createCollectionRecord($loan);
        }

        return $overdueLoans;
    }

    /**
     * Create collection record for overdue loan
     */
    public function createCollectionRecord($loan)
    {
        $daysOverdue = now()->diffInDays($loan->due_date);
        $penaltyAmount = $this->calculatePenalty($loan, $daysOverdue);

        return Collection::create([
            'loan_id' => $loan->id,
            'client_id' => $loan->client_id,
            'overdue_amount' => $loan->outstanding_balance,
            'penalty_amount' => $penaltyAmount,
            'total_due' => $loan->outstanding_balance + $penaltyAmount,
            'days_overdue' => $daysOverdue,
            'status' => 'pending',
            'assigned_to' => $this->assignCollector($loan),
            'created_at' => now(),
        ]);
    }

    /**
     * Calculate penalty for overdue loan
     */
    public function calculatePenalty($loan, $daysOverdue)
    {
        $penaltyRate = $loan->penalty_rate ?? 0.05; // 5% per day default
        return $loan->outstanding_balance * $penaltyRate * $daysOverdue;
    }

    /**
     * Assign collector based on loan amount and branch
     */
    public function assignCollector($loan)
    {
        // Simple assignment logic - in real system, this would be more sophisticated
        $branchId = $loan->branch_id;
        
        // Get loan officers from the same branch
        $collectors = \App\Models\User::where('branch_id', $branchId)
            ->whereHas('roles', function($query) {
                $query->whereIn('name', ['loan_officer', 'branch_manager']);
            })
            ->get();

        return $collectors->first()?->id;
    }

    /**
     * Process collection actions
     */
    public function processCollectionAction($collectionId, $action, $notes = null)
    {
        $collection = Collection::findOrFail($collectionId);
        
        $recoveryAction = RecoveryAction::create([
            'collection_id' => $collectionId,
            'action_type' => $action,
            'action_date' => now(),
            'notes' => $notes,
            'performed_by' => auth()->id(),
        ]);

        // Update collection status based on action
        switch ($action) {
            case 'phone_call':
                $collection->update(['status' => 'contacted']);
                break;
            case 'visit':
                $collection->update(['status' => 'visited']);
                break;
            case 'payment_received':
                $collection->update(['status' => 'resolved']);
                break;
            case 'legal_action':
                $collection->update(['status' => 'legal']);
                break;
        }

        return $recoveryAction;
    }

    /**
     * Send collection notifications
     */
    public function sendCollectionNotifications()
    {
        $overdueLoans = $this->identifyOverdueLoans();
        
        foreach ($overdueLoans as $loan) {
            // Send notification to client
            $loan->client->notify(new OverdueLoanNotification($loan));
            
            // Log communication
            CommunicationLog::create([
                'client_id' => $loan->client_id,
                'type' => 'overdue_notification',
                'message' => "Overdue loan notification sent for loan {$loan->loan_number}",
                'sent_at' => now(),
                'sent_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Get collection performance metrics
     */
    public function getCollectionMetrics($branchId = null, $startDate = null, $endDate = null)
    {
        $query = Collection::query();
        
        if ($branchId) {
            $query->whereHas('loan', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $collections = $query->get();

        return [
            'total_collections' => $collections->count(),
            'resolved_collections' => $collections->where('status', 'resolved')->count(),
            'pending_collections' => $collections->where('status', 'pending')->count(),
            'total_overdue_amount' => $collections->sum('overdue_amount'),
            'total_penalty_amount' => $collections->sum('penalty_amount'),
            'collection_rate' => $collections->count() > 0 ? 
                ($collections->where('status', 'resolved')->count() / $collections->count()) * 100 : 0,
            'average_days_overdue' => $collections->avg('days_overdue'),
        ];
    }

    /**
     * Get collection trends
     */
    public function getCollectionTrends($months = 12)
    {
        $startDate = now()->subMonths($months);
        
        return Collection::where('created_at', '>=', $startDate)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count, SUM(overdue_amount) as total_amount')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Escalate collection cases
     */
    public function escalateCollectionCases()
    {
        $escalatedCollections = Collection::where('status', 'pending')
            ->where('days_overdue', '>', 30)
            ->get();

        foreach ($escalatedCollections as $collection) {
            // Update status to escalated
            $collection->update(['status' => 'escalated']);
            
            // Create escalation action
            RecoveryAction::create([
                'collection_id' => $collection->id,
                'action_type' => 'escalation',
                'action_date' => now(),
                'notes' => 'Automatically escalated due to extended overdue period',
                'performed_by' => null, // System action
            ]);
        }

        return $escalatedCollections;
    }

    /**
     * Generate collection report
     */
    public function generateCollectionReport($branchId = null, $startDate = null, $endDate = null)
    {
        $query = Collection::with(['loan.client', 'recoveryActions']);
        
        if ($branchId) {
            $query->whereHas('loan', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->get();
    }
}
