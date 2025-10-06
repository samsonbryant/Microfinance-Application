<?php

namespace App\Jobs;

use App\Models\Staff;
use App\Models\Payroll;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessMonthlyPayroll implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $month;

    /**
     * Create a new job instance.
     */
    public function __construct($month = null)
    {
        $this->month = $month ?? Carbon::now()->format('Y-m');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Processing monthly payroll for {$this->month}");

        $staff = Staff::where('status', 'active')->get();
        $processedCount = 0;

        foreach ($staff as $employee) {
            // Check if payroll already exists for this month
            $existingPayroll = Payroll::where('staff_id', $employee->id)
                ->where('month', $this->month)
                ->first();

            if ($existingPayroll) {
                continue; // Skip if already processed
            }

            // Create payroll record
            $payroll = Payroll::create([
                'staff_id' => $employee->id,
                'month' => $this->month,
                'basic_salary' => $employee->salary,
                'allowances' => 0, // Can be calculated based on business rules
                'deductions' => 0, // Can be calculated based on business rules
                'net_salary' => $employee->salary,
                'status' => 'pending',
            ]);

            $processedCount++;
        }

        Log::info("Processed {$processedCount} payroll records for {$this->month}");
    }
}