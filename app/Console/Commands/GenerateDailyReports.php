<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GenerateDailyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily reports for the microfinance system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating daily reports...');

        $dashboardService = app(DashboardService::class);
        $date = Carbon::now()->format('Y-m-d');

        // Generate admin metrics
        $adminMetrics = $dashboardService->getMetrics('admin');
        
        // Generate branch manager metrics
        $branchMetrics = $dashboardService->getMetrics('branch_manager');
        
        // Generate loan officer metrics
        $loanOfficerMetrics = $dashboardService->getMetrics('loan_officer');

        // Create daily report data
        $reportData = [
            'date' => $date,
            'admin_metrics' => $adminMetrics,
            'branch_metrics' => $branchMetrics,
            'loan_officer_metrics' => $loanOfficerMetrics,
            'generated_at' => now()->toISOString(),
        ];

        // Save report to storage
        $filename = "daily-reports/report-{$date}.json";
        Storage::put($filename, json_encode($reportData, JSON_PRETTY_PRINT));

        $this->info("Daily report generated: {$filename}");
        
        // Generate summary
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Clients', $adminMetrics['total_clients'] ?? 0],
                ['Active Loans', $adminMetrics['active_loans'] ?? 0],
                ['Overdue Loans', $adminMetrics['overdue_loans'] ?? 0],
                ['Portfolio Value', '$' . number_format(($adminMetrics['total_outstanding'] ?? 0) / 1000, 1) . 'K'],
            ]
        );

        $this->info('Daily reports generation completed successfully.');
    }
}