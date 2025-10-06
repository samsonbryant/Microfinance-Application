<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ClearOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:clear {--days=30 : Number of days to keep logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear old log files older than specified days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Clearing log files older than {$days} days...");

        $logPath = storage_path('logs');
        $deletedCount = 0;

        if (File::exists($logPath)) {
            $files = File::files($logPath);
            
            foreach ($files as $file) {
                $fileDate = Carbon::createFromTimestamp($file->getMTime());
                
                if ($fileDate->lt($cutoffDate)) {
                    File::delete($file->getPathname());
                    $deletedCount++;
                    $this->line("Deleted: " . $file->getFilename());
                }
            }
        }

        $this->info("Cleared {$deletedCount} old log files.");
        
        // Also clear Laravel log cache
        $this->call('cache:clear');
        
        $this->info('Log cleanup completed successfully.');
    }
}