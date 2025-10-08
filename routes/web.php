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
    Route::post('clients/{client}/verify-kyc', [App\Http\Controllers\ClientController::class, 'verifyKyc'])->name('clients.verify-kyc');
    Route::post('clients/{client}/suspend', [App\Http\Controllers\ClientController::class, 'suspend'])->name('clients.suspend');
    Route::post('clients/{client}/activate', [App\Http\Controllers\ClientController::class, 'activate'])->name('clients.activate');
    
    // Loan Management - All authenticated users
    Route::resource('loans', App\Http\Controllers\LoanController::class);
    Route::post('loans/{loan}/approve', [App\Http\Controllers\LoanController::class, 'approve'])->name('loans.approve');
    Route::post('loans/{loan}/reject', [App\Http\Controllers\LoanController::class, 'reject'])->name('loans.reject');
    Route::post('loans/{loan}/disburse', [App\Http\Controllers\LoanController::class, 'disburse'])->name('loans.disburse');
    Route::post('loans/{loan}/repay', [App\Http\Controllers\LoanController::class, 'repay'])->name('loans.repay');
    
    Route::resource('loan-applications', App\Http\Controllers\LoanApplicationController::class);
    
    // Transaction Management - All authenticated users
    Route::resource('transactions', App\Http\Controllers\TransactionController::class);
    Route::post('transactions/{transaction}/approve', [App\Http\Controllers\TransactionController::class, 'approve'])->name('transactions.approve');
    Route::post('transactions/{transaction}/reject', [App\Http\Controllers\TransactionController::class, 'reject'])->name('transactions.reject');
    Route::post('transactions/{transaction}/reverse', [App\Http\Controllers\TransactionController::class, 'reverse'])->name('transactions.reverse');
    
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
    
    // General Ledger - Admin and General Manager only (Custom routes BEFORE resource)
    Route::prefix('general-ledger')->name('general-ledger.')->middleware('role:admin,general_manager')->group(function () {
        Route::get('/trial-balance', [App\Http\Controllers\GeneralLedgerController::class, 'trialBalance'])->name('trial-balance');
        Route::get('/profit-loss', [App\Http\Controllers\GeneralLedgerController::class, 'profitAndLoss'])->name('profit-loss');
        Route::get('/balance-sheet', [App\Http\Controllers\GeneralLedgerController::class, 'balanceSheet'])->name('balance-sheet');
    });
    Route::resource('general-ledger', App\Http\Controllers\GeneralLedgerController::class)
        ->middleware('role:admin,general_manager');
    
    // Financial Reports - Admin and General Manager only
    Route::resource('financial-reports', App\Http\Controllers\FinancialReportsController::class)
        ->middleware('role:admin,general_manager');
    
    // Reports - Comprehensive reporting system
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('index');
        Route::get('/staff', [App\Http\Controllers\ReportController::class, 'staff'])->name('staff');
        Route::get('/financial', [App\Http\Controllers\ReportController::class, 'financial'])->name('financial');
        Route::get('/clients', [App\Http\Controllers\ReportController::class, 'clients'])->name('clients');
        Route::get('/portfolio', [App\Http\Controllers\ReportController::class, 'portfolio'])->name('portfolio');
        Route::get('/collections', [App\Http\Controllers\ReportController::class, 'collections'])->name('collections');
        Route::get('/performance', [App\Http\Controllers\ReportController::class, 'performance'])->name('performance');
        Route::get('/export-excel/{type}', [App\Http\Controllers\ReportController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export-pdf/{type}', [App\Http\Controllers\ReportController::class, 'exportPdf'])->name('export-pdf');
    });
    
    // Settings - Admin only
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\SettingsController::class, 'index'])->name('index');
    })->middleware('role:admin');
    
    // Audit Logs - Admin only
    Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('/', [App\Http\Controllers\AuditLogController::class, 'index'])->name('index');
    })->middleware('role:admin');
    
    // Backup - Admin only
    Route::prefix('backup')->name('backup.')->group(function () {
        Route::get('/', [App\Http\Controllers\BackupController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\BackupController::class, 'create'])->name('create');
        Route::post('/create', [App\Http\Controllers\BackupController::class, 'store'])->name('store');
        Route::post('/{backup}/restore', [App\Http\Controllers\BackupController::class, 'restore'])->name('restore');
        Route::delete('/{backup}', [App\Http\Controllers\BackupController::class, 'destroy'])->name('destroy');
    })->middleware('role:admin');
    
    // Staff Management - HR and Admin only
    Route::resource('staff', App\Http\Controllers\StaffController::class)
        ->middleware('role:admin,hr');
    
    // Payroll Management - HR and Admin only
    Route::resource('payrolls', App\Http\Controllers\PayrollController::class)
        ->middleware('role:admin,hr');
    
    // Savings Accounts
    Route::resource('savings-accounts', App\Http\Controllers\SavingsAccountController::class);
    Route::post('savings-accounts/{savingsAccount}/deposit', [App\Http\Controllers\SavingsAccountController::class, 'deposit'])->name('savings-accounts.deposit');
    Route::post('savings-accounts/{savingsAccount}/withdraw', [App\Http\Controllers\SavingsAccountController::class, 'withdraw'])->name('savings-accounts.withdraw');
    Route::post('savings-accounts/{savingsAccount}/close', [App\Http\Controllers\SavingsAccountController::class, 'close'])->name('savings-accounts.close');
    
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
    
    // Additional Admin Routes
    Route::resource('kyc-documents', App\Http\Controllers\KycDocumentController::class)
        ->middleware('role:admin,general_manager,loan_officer');
    
    Route::resource('client-risk-profiles', App\Http\Controllers\ClientRiskProfileController::class)
        ->middleware('role:admin,general_manager,loan_officer');
    
    Route::resource('loan-repayments', App\Http\Controllers\LoanRepaymentController::class)
        ->middleware('role:admin,general_manager,loan_officer');
    
    Route::resource('collaterals', App\Http\Controllers\CollateralController::class)
        ->middleware('role:admin,general_manager,loan_officer');
    
    Route::resource('approval-workflows', App\Http\Controllers\ApprovalWorkflowController::class)
        ->middleware('role:admin,general_manager');
    
    Route::resource('recovery-actions', App\Http\Controllers\RecoveryActionController::class)
        ->middleware('role:admin,general_manager,loan_officer');
    
    Route::resource('communication-logs', App\Http\Controllers\CommunicationLogController::class)
        ->middleware('role:admin,general_manager,loan_officer');
    
    Route::resource('attendance', App\Http\Controllers\AttendanceController::class)
        ->middleware('role:admin,hr');
    
    Route::resource('performance', App\Http\Controllers\PerformanceController::class)
        ->middleware('role:admin,hr');
    
    Route::prefix('system-health')->name('system-health.')->group(function () {
        Route::get('/', [App\Http\Controllers\SystemHealthController::class, 'index'])->name('index');
    })->middleware('role:admin');
    
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', [App\Http\Controllers\LogController::class, 'index'])->name('index');
    })->middleware('role:admin');
    
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', [App\Http\Controllers\MaintenanceController::class, 'index'])->name('index');
    })->middleware('role:admin');
    
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
