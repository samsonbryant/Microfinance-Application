<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountingController;

/*
|--------------------------------------------------------------------------
| Accounting Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Microbook-G5 Accounting and Financial
| Management Module. All routes are protected by authentication and
| role-based permissions.
|
*/

Route::middleware(['auth', 'verified'])->prefix('accounting')->name('accounting.')->group(function () {
    
    // Accounting Dashboard
    Route::get('/', [AccountingController::class, 'index'])->name('dashboard');
    
    // Chart of Accounts Management
    Route::middleware('permission:manage_chart_of_accounts')->group(function () {
        Route::get('/chart-of-accounts', [AccountingController::class, 'chartOfAccounts'])->name('chart-of-accounts');
        Route::get('/chart-of-accounts/create', [AccountingController::class, 'createAccount'])->name('chart-of-accounts.create');
        Route::post('/chart-of-accounts', [AccountingController::class, 'storeAccount'])->name('chart-of-accounts.store');
        Route::get('/chart-of-accounts/{account}/edit', [AccountingController::class, 'editAccount'])->name('chart-of-accounts.edit');
        Route::put('/chart-of-accounts/{account}', [AccountingController::class, 'updateAccount'])->name('chart-of-accounts.update');
        Route::delete('/chart-of-accounts/{account}', [AccountingController::class, 'deleteAccount'])->name('chart-of-accounts.destroy');
    });
    
    // General Ledger
    Route::middleware('permission:view_accounting')->group(function () {
        Route::get('/general-ledger', [AccountingController::class, 'generalLedger'])->name('general-ledger');
    });
    
    // Financial Reports
    Route::middleware('permission:view_financial_reports')->group(function () {
        Route::get('/financial-reports', [AccountingController::class, 'financialReports'])->name('financial-reports');
    });
    
    // Journal Entries Management
    Route::middleware('permission:manage_journal_entries')->group(function () {
        Route::get('/journal-entries', [AccountingController::class, 'journalEntries'])->name('journal-entries');
        Route::get('/journal-entries/create', [AccountingController::class, 'createJournalEntry'])->name('journal-entries.create');
        Route::post('/journal-entries', [AccountingController::class, 'storeJournalEntry'])->name('journal-entries.store');
        Route::post('/journal-entries/{journalEntry}/approve', [AccountingController::class, 'approveJournalEntry'])->name('journal-entries.approve');
        Route::post('/journal-entries/{journalEntry}/post', [AccountingController::class, 'postJournalEntry'])->name('journal-entries.post');
    });
    
    // Expense Entries Management
    Route::middleware('permission:manage_expenses')->group(function () {
        Route::get('/expense-entries', [AccountingController::class, 'expenseEntries'])->name('expense-entries');
        Route::get('/expense-entries/create', [AccountingController::class, 'createExpenseEntry'])->name('expense-entries.create');
        Route::post('/expense-entries', [AccountingController::class, 'storeExpenseEntry'])->name('expense-entries.store');
        Route::post('/expense-entries/{expenseEntry}/approve', [AccountingController::class, 'approveExpenseEntry'])->name('expense-entries.approve');
        Route::post('/expense-entries/{expenseEntry}/post', [AccountingController::class, 'postExpenseEntry'])->name('expense-entries.post');
    });
    
    // Reconciliation Management
    Route::middleware('permission:manage_reconciliations')->group(function () {
        Route::get('/reconciliations', [App\Http\Controllers\ReconciliationController::class, 'index'])->name('reconciliations');
        Route::get('/reconciliations/create', [App\Http\Controllers\ReconciliationController::class, 'create'])->name('reconciliations.create');
        Route::post('/reconciliations', [App\Http\Controllers\ReconciliationController::class, 'store'])->name('reconciliations.store');
        Route::get('/reconciliations/{reconciliation}', [App\Http\Controllers\ReconciliationController::class, 'show'])->name('reconciliations.show');
        Route::post('/reconciliations/{reconciliation}/start', [App\Http\Controllers\ReconciliationController::class, 'start'])->name('reconciliations.start');
        Route::post('/reconciliations/{reconciliation}/complete', [App\Http\Controllers\ReconciliationController::class, 'complete'])->name('reconciliations.complete');
        Route::post('/reconciliations/{reconciliation}/approve', [App\Http\Controllers\ReconciliationController::class, 'approve'])->name('reconciliations.approve');
    });
    
    // Advanced Reports
    Route::middleware('permission:view_financial_reports')->group(function () {
        Route::get('/reports', [App\Http\Controllers\ReportsController::class, 'index'])->name('reports');
        Route::get('/reports/{reportType}', [App\Http\Controllers\ReportsController::class, 'show'])->name('reports.show');
        Route::get('/reports/{reportType}/export/{format}', [App\Http\Controllers\ReportsController::class, 'export'])->name('reports.export');
    });
    
    // Audit Trail
    Route::middleware('permission:view_audit_trail')->group(function () {
        Route::get('/audit-trail', [App\Http\Controllers\AuditTrailController::class, 'index'])->name('audit-trail');
        Route::get('/audit-trail/{activity}', [App\Http\Controllers\AuditTrailController::class, 'show'])->name('audit-trail.show');
        Route::get('/audit-trail/export', [App\Http\Controllers\AuditTrailController::class, 'export'])->name('audit-trail.export');
    });
    
    // Banks Management
    Route::middleware('permission:manage_banks')->group(function () {
        Route::get('/banks', [App\Http\Controllers\BankController::class, 'index'])->name('banks.index');
        Route::get('/banks/create', [App\Http\Controllers\BankController::class, 'create'])->name('banks.create');
        Route::post('/banks', [App\Http\Controllers\BankController::class, 'store'])->name('banks.store');
        Route::get('/banks/{bank}', [App\Http\Controllers\BankController::class, 'show'])->name('banks.show');
        Route::get('/banks/{bank}/edit', [App\Http\Controllers\BankController::class, 'edit'])->name('banks.edit');
        Route::put('/banks/{bank}', [App\Http\Controllers\BankController::class, 'update'])->name('banks.update');
        Route::delete('/banks/{bank}', [App\Http\Controllers\BankController::class, 'destroy'])->name('banks.destroy');
    });

    // Expenses Management
    Route::middleware('permission:manage_expenses')->group(function () {
        Route::get('/expenses', [App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/create', [App\Http\Controllers\ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [App\Http\Controllers\ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/expenses/{expense}', [App\Http\Controllers\ExpenseController::class, 'show'])->name('expenses.show');
        Route::post('/expenses/{expense}/approve', [App\Http\Controllers\ExpenseController::class, 'approve'])->name('expenses.approve');
        Route::post('/expenses/{expense}/post', [App\Http\Controllers\ExpenseController::class, 'post'])->name('expenses.post');
        Route::post('/expenses/{expense}/reject', [App\Http\Controllers\ExpenseController::class, 'reject'])->name('expenses.reject');
    });

    // Revenues Management
    Route::middleware('permission:manage_revenues')->group(function () {
        Route::get('/revenues', [App\Http\Controllers\RevenueController::class, 'index'])->name('revenues.index');
        Route::get('/revenues/create', [App\Http\Controllers\RevenueController::class, 'create'])->name('revenues.create');
        Route::post('/revenues', [App\Http\Controllers\RevenueController::class, 'store'])->name('revenues.store');
        Route::get('/revenues/{revenue}', [App\Http\Controllers\RevenueController::class, 'show'])->name('revenues.show');
        Route::post('/revenues/{revenue}/approve', [App\Http\Controllers\RevenueController::class, 'approve'])->name('revenues.approve');
        Route::post('/revenues/{revenue}/post', [App\Http\Controllers\RevenueController::class, 'post'])->name('revenues.post');
        Route::post('/revenues/{revenue}/reject', [App\Http\Controllers\RevenueController::class, 'reject'])->name('revenues.reject');
    });

    // Transfers Management
    Route::middleware('permission:manage_transfers')->group(function () {
        Route::get('/transfers', [App\Http\Controllers\TransferController::class, 'index'])->name('transfers.index');
        Route::get('/transfers/create', [App\Http\Controllers\TransferController::class, 'create'])->name('transfers.create');
        Route::post('/transfers', [App\Http\Controllers\TransferController::class, 'store'])->name('transfers.store');
        Route::get('/transfers/{transfer}', [App\Http\Controllers\TransferController::class, 'show'])->name('transfers.show');
        Route::post('/transfers/{transfer}/approve', [App\Http\Controllers\TransferController::class, 'approve'])->name('transfers.approve');
        Route::post('/transfers/{transfer}/post', [App\Http\Controllers\TransferController::class, 'post'])->name('transfers.post');
        Route::post('/transfers/{transfer}/reject', [App\Http\Controllers\TransferController::class, 'reject'])->name('transfers.reject');
    });

    // Financial Reports
    Route::middleware('permission:view_financial_reports')->group(function () {
        Route::get('/reports/financial', [App\Http\Controllers\FinancialReportController::class, 'index'])->name('reports.financial');
        Route::get('/reports/profit-loss', [App\Http\Controllers\FinancialReportController::class, 'profitAndLoss'])->name('reports.profit-loss');
        Route::get('/reports/balance-sheet', [App\Http\Controllers\FinancialReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
        Route::get('/reports/cash-flow', [App\Http\Controllers\FinancialReportController::class, 'cashFlow'])->name('reports.cash-flow');
        Route::get('/reports/revenue-board', [App\Http\Controllers\FinancialReportController::class, 'revenueBoard'])->name('reports.revenue-board');
        
        // Export routes
        Route::get('/reports/profit-loss/export/{format}', [App\Http\Controllers\FinancialReportController::class, 'exportProfitAndLoss'])->name('reports.profit-loss.export');
        Route::get('/reports/balance-sheet/export/{format}', [App\Http\Controllers\FinancialReportController::class, 'exportBalanceSheet'])->name('reports.balance-sheet.export');
        Route::get('/reports/cash-flow/export/{format}', [App\Http\Controllers\FinancialReportController::class, 'exportCashFlow'])->name('reports.cash-flow.export');
    });
    
    // API Routes for AJAX requests
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/account-balance/{accountId}', [AccountingController::class, 'getAccountBalance'])->name('account-balance');
        Route::get('/trial-balance', [AccountingController::class, 'getTrialBalance'])->name('trial-balance');
        Route::get('/metrics', [App\Http\Controllers\Api\AccountingApiController::class, 'getMetrics'])->name('metrics');
        Route::get('/revenue-breakdown', [App\Http\Controllers\Api\AccountingApiController::class, 'getRevenueBreakdown'])->name('revenue-breakdown');
        Route::get('/cash-position', [App\Http\Controllers\Api\AccountingApiController::class, 'getCashPosition'])->name('cash-position');
    });
});
