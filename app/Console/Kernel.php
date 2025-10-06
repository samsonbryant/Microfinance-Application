<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ProcessOverdueLoans;
use App\Jobs\SendDailyNotifications;
use App\Jobs\ProcessMonthlyPayroll;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Process overdue loans every hour
        $schedule->job(new ProcessOverdueLoans)->hourly();

        // Send daily notifications at 9 AM
        $schedule->job(new SendDailyNotifications)->dailyAt('09:00');

        // Process monthly payroll on the 1st of each month at 8 AM
        $schedule->job(new ProcessMonthlyPayroll)->monthlyOn(1, '08:00');

        // Clear expired cache every day at midnight
        $schedule->command('cache:clear')->dailyAt('00:00');

        // Clear expired sessions every day at 1 AM
        $schedule->command('session:gc')->dailyAt('01:00');

        // Generate daily reports at 6 PM
        $schedule->command('reports:daily')->dailyAt('18:00');

        // Backup database every day at 2 AM
        $schedule->command('backup:run')->dailyAt('02:00');

        // Clean up old logs every week
        $schedule->command('log:clear')->weekly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
