<?php

namespace App\Jobs;

use App\Models\Loan;
use App\Services\NotificationService;
use App\Services\LoanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOverdueLoans implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing overdue loans job started');

        $loanService = app(LoanService::class);
        $notificationService = app(NotificationService::class);

        // Check for overdue loans and update their status
        $overdueCount = $loanService->checkOverdueLoans();
        
        if ($overdueCount > 0) {
            Log::info("Found {$overdueCount} overdue loans");
            
            // Get all overdue loans and send notifications
            $overdueLoans = Loan::where('status', 'overdue')
                ->with(['client.user'])
                ->get();
            
            foreach ($overdueLoans as $loan) {
                $notificationService->notifyOverdueLoan($loan);
            }
        }

        Log::info('Processing overdue loans job completed');
    }
}