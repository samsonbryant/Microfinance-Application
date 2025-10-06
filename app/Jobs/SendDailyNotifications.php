<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDailyNotifications implements ShouldQueue
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
        Log::info('Daily notifications job started');

        $notificationService = app(NotificationService::class);

        // Send daily due reminders
        $dueCount = $notificationService->sendDailyDueReminders();
        Log::info("Sent {$dueCount} due loan reminders");

        // Send bulk overdue notifications
        $overdueCount = $notificationService->sendBulkOverdueNotifications();
        Log::info("Sent {$overdueCount} overdue loan notifications");

        Log::info('Daily notifications job completed');
    }
}