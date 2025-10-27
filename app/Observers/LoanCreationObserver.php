<?php

namespace App\Observers;

use App\Models\Loan;
use App\Services\LoanCalculationService;
use App\Events\LoanApplicationSubmitted;
use App\Events\LoanReviewed;
use App\Events\LoanApprovedEvent;
use App\Events\LoanDisbursed;
use App\Events\LoanUpdated;

class LoanCreationObserver
{
    protected $calculationService;

    public function __construct(LoanCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Handle the Loan "creating" event (before save)
     */
    public function creating(Loan $loan): void
    {
        // Set initial values if not set
        if (!$loan->principal_amount && $loan->amount) {
            $loan->principal_amount = $loan->amount;
        }
        
        if (!$loan->interest_rate) {
            $loan->interest_rate = 12; // Default 12% annual
        }
        
        if (!$loan->loan_number) {
            $loan->loan_number = 'L' . now()->format('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Handle the Loan "created" event
     */
    public function created(Loan $loan): void
    {
        // Calculate amortization schedule and update loan
        if ($loan->principal_amount && $loan->loan_term) {
            $this->calculationService->updateLoanCalculations($loan);
        }
        
        // Broadcast based on initial status
        if ($loan->status === 'pending') {
            broadcast(new LoanApplicationSubmitted($loan))->toOthers();
        }
    }

    /**
     * Handle the Loan "updated" event
     */
    public function updated(Loan $loan): void
    {
        try {
            // Recalculate if loan terms changed (not calculated fields)
            if ($loan->wasChanged(['principal_amount', 'interest_rate', 'loan_term', 'disbursement_date'])) {
                $this->calculationService->updateLoanCalculations($loan);
            }
            
            // Broadcast workflow events
            if ($loan->wasChanged('status')) {
                switch ($loan->status) {
                    case 'under_review':
                        broadcast(new LoanReviewed($loan, $loan->reviewed_by ?? auth()->id()))->toOthers();
                        $this->sendNotifications($loan, 'reviewed');
                        break;
                        
                    case 'approved':
                        broadcast(new LoanApprovedEvent($loan))->toOthers();
                        $this->sendNotifications($loan, 'approved');
                        break;
                        
                    case 'rejected':
                        $this->sendNotifications($loan, 'rejected');
                        break;
                        
                    case 'active':
                        // When status changes to active with disbursement_date, it means loan was disbursed
                        if ($loan->disbursement_date && $loan->wasChanged('disbursement_date')) {
                            broadcast(new LoanDisbursed($loan))->toOthers();
                            $this->sendNotifications($loan, 'disbursed');
                        }
                        break;
                }
            }
            
            broadcast(new LoanUpdated($loan))->toOthers();
        } catch (\Exception $e) {
            \Log::error('LoanCreationObserver::updated error: ' . $e->getMessage(), [
                'loan_id' => $loan->id,
                'changed' => $loan->getChanges()
            ]);
        }
    }

    /**
     * Send notifications based on loan status - REAL-TIME to all parties
     */
    private function sendNotifications(Loan $loan, $event)
    {
        try {
            switch ($event) {
                case 'reviewed':
                    // Loan Officer has reviewed - notify borrower, branch manager, and admin
                    if ($loan->client && $loan->client->user) {
                        $loan->client->user->notify(new \App\Notifications\LoanApplicationNotification($loan, 'documents_added'));
                    }
                    
                    // Notify branch manager
                    $managers = \App\Models\User::role('branch_manager')
                        ->where('branch_id', $loan->branch_id)
                        ->get();
                    foreach ($managers as $manager) {
                        $manager->notify(new \App\Notifications\LoanApplicationNotification($loan, 'reviewed'));
                    }
                    
                    // Notify admin
                    $admins = \App\Models\User::role('admin')->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new \App\Notifications\LoanApplicationNotification($loan, 'reviewed'));
                    }
                    break;
                    
                case 'approved':
                    // Branch manager approved - notify borrower, loan officer, and admin
                    if ($loan->client && $loan->client->user) {
                        $loan->client->user->notify(new \App\Notifications\LoanApplicationNotification($loan, 'kyc_verified'));
                    }
                    
                    // Notify loan officer
                    if ($loan->createdBy) {
                        $loan->createdBy->notify(new \App\Notifications\LoanApplicationNotification($loan, 'approved'));
                    }
                    
                    // Notify admin for final approval
                    $admins = \App\Models\User::role('admin')->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new \App\Notifications\LoanApplicationNotification($loan, 'approved'));
                    }
                    
                    // Notify branch manager
                    $managers = \App\Models\User::role('branch_manager')
                        ->where('branch_id', $loan->branch_id)
                        ->get();
                    foreach ($managers as $manager) {
                        $manager->notify(new \App\Notifications\LoanApplicationNotification($loan, 'approved'));
                    }
                    break;
                    
                case 'rejected':
                    // Notify borrower and loan officer
                    if ($loan->client && $loan->client->user) {
                        $loan->client->user->notify(new \App\Notifications\LoanApplicationNotification($loan, 'rejected'));
                    }
                    if ($loan->createdBy) {
                        $loan->createdBy->notify(new \App\Notifications\LoanApplicationNotification($loan, 'rejected'));
                    }
                    break;
                    
                case 'disbursed':
                    // Admin disbursed - notify EVERYONE in real-time
                    if ($loan->client && $loan->client->user) {
                        $loan->client->user->notify(new \App\Notifications\LoanApplicationNotification($loan, 'disbursed'));
                    }
                    if ($loan->createdBy) {
                        $loan->createdBy->notify(new \App\Notifications\LoanApplicationNotification($loan, 'disbursed'));
                    }
                    $managers = \App\Models\User::role('branch_manager')
                        ->where('branch_id', $loan->branch_id)
                        ->get();
                    foreach ($managers as $manager) {
                        $manager->notify(new \App\Notifications\LoanApplicationNotification($loan, 'disbursed'));
                    }
                    $admins = \App\Models\User::role('admin')->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new \App\Notifications\LoanApplicationNotification($loan, 'disbursed'));
                    }
                    break;
            }
        } catch (\Exception $e) {
            \Log::error('Error sending loan notification: ' . $e->getMessage());
        }
    }
}

