<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Expense;
use App\Models\Transfer;
use App\Models\RevenueEntry;
use App\Models\JournalEntry;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Observers\ExpenseObserver;
use App\Observers\TransferObserver;
use App\Observers\RevenueEntryObserver;
use App\Observers\JournalEntryObserver;
use App\Observers\LoanObserver;
use App\Observers\LoanRepaymentObserver;
use App\Observers\LoanCreationObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Accounting Model Observers
        Expense::observe(ExpenseObserver::class);
        Transfer::observe(TransferObserver::class);
        RevenueEntry::observe(RevenueEntryObserver::class);
        JournalEntry::observe(JournalEntryObserver::class);
        
        // Register Loan Observers for Workflow and Accounting Integration
        Loan::observe(LoanCreationObserver::class); // Primary observer for calculations and workflow
        Loan::observe(LoanObserver::class); // Accounting integration
        LoanRepayment::observe(LoanRepaymentObserver::class);
    }
}
