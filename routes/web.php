<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});




Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Branch Management - Admin and General Manager only
    Route::resource('branches', App\Http\Controllers\BranchController::class)
        ->middleware('role:admin,general_manager');
    
    // Client Management - All authenticated users
    Route::resource('clients', App\Http\Controllers\ClientController::class);
    
    // Loan Management - All authenticated users
    Route::resource('loans', App\Http\Controllers\LoanController::class);
    Route::resource('loan-applications', App\Http\Controllers\LoanApplicationController::class);
    
    // Transaction Management - All authenticated users
    Route::resource('transactions', App\Http\Controllers\TransactionController::class);
    
    // User Management - Admin only
    Route::resource('users', App\Http\Controllers\UsersController::class)
        ->middleware('role:admin');
    
    // Collections Management - Loan Officers and above
    Route::resource('collections', App\Http\Controllers\CollectionsController::class)
        ->middleware('role:admin,general_manager,loan_officer');
    
    // Chart of Accounts - Admin and General Manager only
    Route::resource('chart-of-accounts', App\Http\Controllers\ChartOfAccountsController::class)
        ->middleware('role:admin,general_manager');
    Route::post('chart-of-accounts/{chartOfAccount}/toggle-status', [App\Http\Controllers\ChartOfAccountsController::class, 'toggleStatus'])
        ->name('chart-of-accounts.toggle-status')
        ->middleware('role:admin,general_manager');
    Route::get('chart-of-accounts/{chartOfAccount}/balance', [App\Http\Controllers\ChartOfAccountsController::class, 'getBalance'])
        ->name('chart-of-accounts.balance')
        ->middleware('role:admin,general_manager');
    
    // General Ledger - Admin and General Manager only
    Route::resource('general-ledger', App\Http\Controllers\GeneralLedgerController::class)
        ->middleware('role:admin,general_manager');
    Route::get('general-ledger/trial-balance', [App\Http\Controllers\GeneralLedgerController::class, 'trialBalance'])
        ->name('general-ledger.trial-balance')
        ->middleware('role:admin,general_manager');
    Route::get('general-ledger/profit-loss', [App\Http\Controllers\GeneralLedgerController::class, 'profitAndLoss'])
        ->name('general-ledger.profit-loss')
        ->middleware('role:admin,general_manager');
    Route::get('general-ledger/balance-sheet', [App\Http\Controllers\GeneralLedgerController::class, 'balanceSheet'])
        ->name('general-ledger.balance-sheet')
        ->middleware('role:admin,general_manager');
    
    // Financial Reports - Admin and General Manager only
    Route::resource('financial-reports', App\Http\Controllers\FinancialReportsController::class)
        ->middleware('role:admin,general_manager');
    
        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('index');
            Route::get('/staff', [App\Http\Controllers\ReportController::class, 'staff'])->name('staff');
            Route::get('/financial', [App\Http\Controllers\ReportController::class, 'financial'])->name('financial');
            Route::get('/clients', [App\Http\Controllers\ReportController::class, 'clients'])->name('clients');
            Route::get('/export-excel/{type}', [App\Http\Controllers\ReportController::class, 'exportExcel'])->name('export-excel');
            Route::get('/export-pdf/{type}', [App\Http\Controllers\ReportController::class, 'exportPdf'])->name('export-pdf');
        });
    
    // Settings - Admin only
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\SettingsController::class, 'index'])->name('index');
    })->middleware('role:admin');
    
    // Audit Logs - Admin only
    Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('/', function() {
            return view('audit-logs.index');
        })->name('index');
    })->middleware('role:admin');
    
    // Backup - Admin only
    Route::prefix('backup')->name('backup.')->group(function () {
        Route::post('/create', function() {
            return back()->with('success', 'Backup created successfully.');
        })->name('create');
    })->middleware('role:admin');
    
    // Staff Management - HR and Admin only
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', function() {
            return view('staff.index');
        })->name('index');
        Route::get('/create', function() {
            return view('staff.create');
        })->name('create');
    })->middleware('role:admin,hr');
    
    // Payroll Management - HR and Admin only
    Route::prefix('payrolls')->name('payrolls.')->group(function () {
        Route::get('/', function() {
            return view('payrolls.index');
        })->name('index');
        Route::get('/create', function() {
            return view('payrolls.create');
        })->name('create');
    })->middleware('role:admin,hr');
    
    // Savings Accounts
    Route::resource('savings-accounts', App\Http\Controllers\SavingsAccountController::class);
    
    // Payments
    Route::resource('payments', App\Http\Controllers\PaymentController::class);
    
    // Profile
    Route::get('/profile/show', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/send-test', [App\Http\Controllers\NotificationController::class, 'sendTest'])->name('send-test');
        Route::post('/send-bulk', [App\Http\Controllers\NotificationController::class, 'sendBulk'])->name('send-bulk');
    });
    
    // Additional Admin Routes - Admin only
    Route::prefix('kyc-documents')->name('kyc-documents.')->group(function () {
        Route::get('/', function() { return view('kyc-documents.index'); })->name('index');
    })->middleware('role:admin');
    
    Route::prefix('client-risk-profiles')->name('client-risk-profiles.')->group(function () {
        Route::get('/', function() { return view('client-risk-profiles.index'); })->name('index');
    })->middleware('role:admin');
    
    Route::prefix('loan-repayments')->name('loan-repayments.')->group(function () {
        Route::get('/', function() { return view('loan-repayments.index'); })->name('index');
    })->middleware('role:admin,general_manager,loan_officer');
    
    Route::prefix('collaterals')->name('collaterals.')->group(function () {
        Route::get('/', function() { return view('collaterals.index'); })->name('index');
    })->middleware('role:admin,general_manager,loan_officer');
    
    Route::prefix('approval-workflows')->name('approval-workflows.')->group(function () {
        Route::get('/', function() { return view('approval-workflows.index'); })->name('index');
    })->middleware('role:admin,general_manager');
    
    Route::prefix('recovery-actions')->name('recovery-actions.')->group(function () {
        Route::get('/', function() { return view('recovery-actions.index'); })->name('index');
    })->middleware('role:admin,general_manager,loan_officer');
    
    Route::prefix('communication-logs')->name('communication-logs.')->group(function () {
        Route::get('/', function() { return view('communication-logs.index'); })->name('index');
    })->middleware('role:admin,general_manager,loan_officer');
    
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', function() { return view('attendance.index'); })->name('index');
    })->middleware('role:admin,hr');
    
    Route::prefix('performance')->name('performance.')->group(function () {
        Route::get('/', function() { return view('performance.index'); })->name('index');
    })->middleware('role:admin,hr');
    
    Route::prefix('system-health')->name('system-health.')->group(function () {
        Route::get('/', function() { return view('system-health.index'); })->name('index');
    })->middleware('role:admin');
    
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', function() { return view('logs.index'); })->name('index');
    })->middleware('role:admin');
    
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', function() { return view('maintenance.index'); })->name('index');
    })->middleware('role:admin');
    
    // Additional Report Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/portfolio', function() { return view('reports.portfolio'); })->name('portfolio');
        Route::get('/collections', function() { return view('reports.collections'); })->name('collections');
        Route::get('/performance', function() { return view('reports.performance'); })->name('performance');
    });
    
    // Borrower Portal
    Route::prefix('borrower')->name('borrower.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\BorrowerController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [App\Http\Controllers\BorrowerController::class, 'profile'])->name('profile');
        Route::put('/profile', [App\Http\Controllers\BorrowerController::class, 'updateProfile'])->name('profile.update');
        
        // Loans
        Route::get('/loans', [App\Http\Controllers\BorrowerController::class, 'loans'])->name('loans.index');
        Route::get('/loans/create', [App\Http\Controllers\BorrowerController::class, 'loanApplicationForm'])->name('loans.create');
        Route::post('/loans', [App\Http\Controllers\BorrowerController::class, 'submitLoanApplication'])->name('loans.store');
        Route::get('/loans/{loan}', [App\Http\Controllers\BorrowerController::class, 'showLoan'])->name('loans.show');
        
        // Savings
        Route::get('/savings', [App\Http\Controllers\BorrowerController::class, 'savings'])->name('savings.index');
        Route::get('/savings/{savingsAccount}', [App\Http\Controllers\BorrowerController::class, 'showSavings'])->name('savings.show');
        
        // Transactions
        Route::get('/transactions', [App\Http\Controllers\BorrowerController::class, 'transactions'])->name('transactions.index');
        
        // Payments
        Route::get('/payments/create', [App\Http\Controllers\BorrowerController::class, 'paymentForm'])->name('payments.create');
        Route::post('/payments', [App\Http\Controllers\BorrowerController::class, 'processPayment'])->name('payments.store');
    });
});

require __DIR__.'/auth.php';
