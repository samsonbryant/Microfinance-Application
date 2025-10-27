<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Health check endpoint for Fly.io
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'app' => config('app.name'),
    ]);
});




Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Role-specific Dashboard Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Admin Dashboard - only admin role
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/dashboard/realtime', [App\Http\Controllers\AdminDashboardController::class, 'getRealtimeData'])->name('admin.dashboard.realtime');
        Route::get('/admin/dashboard/export', [App\Http\Controllers\AdminDashboardController::class, 'export'])->name('admin.dashboard.export');
        
        // Real-time loan approval endpoints
        Route::post('/admin/loans/{loan}/approve', [App\Http\Controllers\AdminDashboardController::class, 'approveLoan'])->name('admin.loans.approve');
        Route::post('/admin/loans/{loan}/reject', [App\Http\Controllers\AdminDashboardController::class, 'rejectLoan'])->name('admin.loans.reject');
        
        // Live feed endpoints
        Route::get('/admin/dashboard/pending-approvals', [App\Http\Controllers\AdminDashboardController::class, 'getPendingApprovals'])->name('admin.dashboard.pending-approvals');
        Route::get('/admin/dashboard/live-feed', [App\Http\Controllers\AdminDashboardController::class, 'getLiveFeed'])->name('admin.dashboard.live-feed');
    });

    // Branch Manager Dashboard - only branch_manager role
    Route::middleware('role:branch_manager')->group(function () {
        Route::get('/branch-manager/dashboard', [App\Http\Controllers\BranchManagerDashboardController::class, 'index'])->name('branch-manager.dashboard');
        Route::get('/branch-manager/dashboard/realtime', [App\Http\Controllers\BranchManagerDashboardController::class, 'getRealtimeData'])->name('branch-manager.dashboard.realtime');
        Route::get('/branch-manager/dashboard/export', [App\Http\Controllers\BranchManagerDashboardController::class, 'exportReport'])->name('branch-manager.dashboard.export');
        Route::get('/branch-manager/collections', [App\Http\Controllers\BranchManagerDashboardController::class, 'collections'])->name('branch-manager.collections');
        Route::post('/branch-manager/process-payment', [App\Http\Controllers\BranchManagerDashboardController::class, 'processPayment'])->name('branch-manager.process-payment');
    });

    // Loan Officer Dashboard - only loan_officer role
    Route::middleware('role:loan_officer')->group(function () {
        Route::get('/loan-officer/dashboard', [App\Http\Controllers\LoanOfficerDashboardController::class, 'index'])->name('loan-officer.dashboard');
        Route::get('/loan-officer/dashboard/realtime', [App\Http\Controllers\LoanOfficerDashboardController::class, 'getRealtimeData'])->name('loan-officer.dashboard.realtime');
        Route::get('/loan-officer/dashboard/export', [App\Http\Controllers\LoanOfficerDashboardController::class, 'exportReport'])->name('loan-officer.dashboard.export');
    });
});

// Dashboard API Routes for real-time updates
Route::middleware('auth')->group(function () {
    Route::get('/dashboard/data', [App\Http\Controllers\DashboardController::class, 'getDashboardData'])->name('dashboard.data');
    Route::get('/dashboard/realtime', [App\Http\Controllers\DashboardController::class, 'getRealtimeUpdates'])->name('dashboard.realtime');
    Route::post('/dashboard/clear-cache', [App\Http\Controllers\DashboardController::class, 'clearCache'])->name('dashboard.clear-cache');
    Route::get('/dashboard/export', [App\Http\Controllers\DashboardController::class, 'exportDashboardData'])->name('dashboard.export');
    Route::get('/dashboard/financial-summary', [App\Http\Controllers\DashboardController::class, 'getFinancialSummary'])->name('dashboard.financial-summary');
    Route::get('/dashboard/recent-activities', [App\Http\Controllers\DashboardController::class, 'getRecentActivities'])->name('dashboard.recent-activities');
    Route::get('/dashboard/pending-approvals', [App\Http\Controllers\DashboardController::class, 'getPendingApprovals'])->name('dashboard.pending-approvals');
    Route::get('/dashboard/system-alerts', [App\Http\Controllers\DashboardController::class, 'getSystemAlerts'])->name('dashboard.system-alerts');
    Route::get('/dashboard/chart-data', [App\Http\Controllers\DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    Route::get('/dashboard/branch/{branchId}', [App\Http\Controllers\DashboardController::class, 'getBranchData'])->name('dashboard.branch');
    Route::get('/dashboard/user/{userId}', [App\Http\Controllers\DashboardController::class, 'getUserData'])->name('dashboard.user');
    
    // Real-time Activity Routes
    Route::prefix('realtime')->name('realtime.')->group(function () {
        Route::get('/activities/all', [App\Http\Controllers\RealtimeActivityController::class, 'getAllUserActivities'])->name('activities.all');
        Route::get('/activities/my', [App\Http\Controllers\RealtimeActivityController::class, 'getMyActivities'])->name('activities.my');
        Route::get('/activities/user/{userId}', [App\Http\Controllers\RealtimeActivityController::class, 'getUserActivities'])->name('activities.user');
        Route::get('/activities/branch', [App\Http\Controllers\RealtimeActivityController::class, 'getBranchActivities'])->name('activities.branch');
        Route::get('/activities/financial', [App\Http\Controllers\RealtimeActivityController::class, 'getFinancialActivities'])->name('activities.financial');
        Route::get('/activities/statistics', [App\Http\Controllers\RealtimeActivityController::class, 'getActivityStatistics'])->name('activities.statistics');
        Route::get('/activities/feed', [App\Http\Controllers\RealtimeActivityController::class, 'getActivityFeed'])->name('activities.feed');
        Route::get('/activities/summary', [App\Http\Controllers\RealtimeActivityController::class, 'getActivitySummary'])->name('activities.summary');
        Route::get('/notifications/pending-approvals', [App\Http\Controllers\RealtimeActivityController::class, 'getPendingApprovalNotifications'])->name('notifications.pending-approvals');
        Route::get('/system/health', [App\Http\Controllers\RealtimeActivityController::class, 'getSystemHealthIndicators'])->name('system.health');
        Route::get('/users/active', [App\Http\Controllers\RealtimeActivityController::class, 'getActiveUsers'])->name('users.active');
        Route::get('/branches/activity-summary', [App\Http\Controllers\RealtimeActivityController::class, 'getBranchActivitySummary'])->name('branches.activity-summary');
        Route::get('/updates', [App\Http\Controllers\RealtimeActivityController::class, 'getRealtimeUpdates'])->name('updates');
        Route::post('/cache/clear', [App\Http\Controllers\RealtimeActivityController::class, 'clearActivityCache'])->name('cache.clear');
    });
});

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
    
    // KYC Document Management
    Route::resource('kyc-documents', App\Http\Controllers\KycDocumentController::class);
    Route::post('kyc-documents/{kycDocument}/verify', [App\Http\Controllers\KycDocumentController::class, 'verify'])->name('kyc-documents.verify');
    Route::get('kyc-documents/{kycDocument}/download', [App\Http\Controllers\KycDocumentController::class, 'download'])->name('kyc-documents.download');
    
    // Collateral Management
    Route::resource('collaterals', App\Http\Controllers\CollateralController::class);
    Route::post('collaterals/{collateral}/verify', [App\Http\Controllers\CollateralController::class, 'verify'])->name('collaterals.verify');
    Route::get('collaterals/{collateral}/documents/{index}/download', [App\Http\Controllers\CollateralController::class, 'downloadDocument'])->name('collaterals.documents.download');
    
    // Transaction Management
    Route::resource('transactions', App\Http\Controllers\TransactionController::class);
    Route::post('transactions/{transaction}/approve', [App\Http\Controllers\TransactionController::class, 'approve'])->name('transactions.approve');
    Route::post('transactions/{transaction}/reverse', [App\Http\Controllers\TransactionController::class, 'reverse'])->name('transactions.reverse');
    Route::get('api/transactions', [App\Http\Controllers\TransactionController::class, 'getTransactions'])->name('transactions.api');
    
    // Loan Repayments Management
    Route::get('loan-repayments', [App\Http\Controllers\LoanRepaymentController::class, 'index'])->name('loan-repayments.index');
    Route::get('loan-repayments/create', [App\Http\Controllers\LoanRepaymentController::class, 'create'])->name('loan-repayments.create');
    Route::post('loan-repayments', [App\Http\Controllers\LoanRepaymentController::class, 'store'])->name('loan-repayments.store');
    Route::get('loan-repayments/{repayment}', [App\Http\Controllers\LoanRepaymentController::class, 'show'])->name('loan-repayments.show');
    Route::get('loan-repayments/stats', [App\Http\Controllers\LoanRepaymentController::class, 'getStats'])->name('loan-repayments.stats');
    
    // Risk Assessment Management
    Route::get('risk-assessment', [App\Http\Controllers\RiskAssessmentController::class, 'index'])->name('risk-assessment.index');
    Route::get('risk-assessment/pending', [App\Http\Controllers\RiskAssessmentController::class, 'pending'])->name('risk-assessment.pending');
    Route::get('risk-assessment/{riskProfile}', [App\Http\Controllers\RiskAssessmentController::class, 'show'])->name('risk-assessment.show');
    Route::post('risk-assessment/clients/{client}/assess', [App\Http\Controllers\RiskAssessmentController::class, 'assess'])->name('risk-assessment.assess');
    Route::post('risk-assessment/clients/{client}/reassess', [App\Http\Controllers\RiskAssessmentController::class, 'reassess'])->name('risk-assessment.reassess');
    Route::post('risk-assessment/batch-assess', [App\Http\Controllers\RiskAssessmentController::class, 'batchAssess'])->name('risk-assessment.batch-assess');
    
    // Approval Workflows
    Route::resource('approval-workflows', App\Http\Controllers\ApprovalWorkflowController::class);
    
    // Unified Approval Center (Admin Only)
    Route::get('approval-center', [App\Http\Controllers\ApprovalCenterController::class, 'index'])->name('approval-center.index');
    Route::get('approval-center/stats', [App\Http\Controllers\ApprovalCenterController::class, 'getStats'])->name('approval-center.stats');
    Route::post('approval-center/loans/{loan}/approve', [App\Http\Controllers\ApprovalCenterController::class, 'approveLoan'])->name('approval-center.loans.approve');
    Route::post('approval-center/loans/{loan}/reject', [App\Http\Controllers\ApprovalCenterController::class, 'rejectLoan'])->name('approval-center.loans.reject');
    Route::post('approval-center/savings/{savings}/approve', [App\Http\Controllers\ApprovalCenterController::class, 'approveSavings'])->name('approval-center.savings.approve');
    Route::post('approval-center/savings/{savings}/reject', [App\Http\Controllers\ApprovalCenterController::class, 'rejectSavings'])->name('approval-center.savings.reject');
    Route::post('approval-center/kyc/{kyc}/approve', [App\Http\Controllers\ApprovalCenterController::class, 'approveKyc'])->name('approval-center.kyc.approve');
    Route::post('approval-center/kyc/{kyc}/reject', [App\Http\Controllers\ApprovalCenterController::class, 'rejectKyc'])->name('approval-center.kyc.reject');
    Route::post('approval-center/collateral/{collateral}/approve', [App\Http\Controllers\ApprovalCenterController::class, 'approveCollateral'])->name('approval-center.collateral.approve');
    Route::post('approval-center/collateral/{collateral}/reject', [App\Http\Controllers\ApprovalCenterController::class, 'rejectCollateral'])->name('approval-center.collateral.reject');
    Route::post('approval-center/clients/{client}/approve', [App\Http\Controllers\ApprovalCenterController::class, 'approveClient'])->name('approval-center.clients.approve');
    Route::post('approval-center/clients/{client}/reject', [App\Http\Controllers\ApprovalCenterController::class, 'rejectClient'])->name('approval-center.clients.reject');
    
    // Recovery Actions
    Route::resource('recovery-actions', App\Http\Controllers\RecoveryActionController::class);
    
    // Communication Logs
    Route::resource('communication-logs', App\Http\Controllers\CommunicationLogController::class);
    
    // Staff Management
    Route::resource('staff', App\Http\Controllers\StaffController::class);
    Route::post('staff/{staff}/activate', [App\Http\Controllers\StaffController::class, 'activate'])->name('staff.activate');
    Route::post('staff/{staff}/deactivate', [App\Http\Controllers\StaffController::class, 'deactivate'])->name('staff.deactivate');
    
    // Payroll Management
    Route::resource('payrolls', App\Http\Controllers\PayrollController::class);
    Route::post('payrolls/{payroll}/process', [App\Http\Controllers\PayrollController::class, 'process'])->name('payrolls.process');
    
    // Loan Management - All authenticated users
    Route::resource('loans', App\Http\Controllers\LoanController::class);
    Route::post('loans/{loan}/approve', [App\Http\Controllers\LoanController::class, 'approve'])->name('loans.approve');
    Route::post('loans/{loan}/reject', [App\Http\Controllers\LoanController::class, 'reject'])->name('loans.reject');
    Route::post('loans/{loan}/disburse', [App\Http\Controllers\LoanController::class, 'disburse'])->name('loans.disburse');
    Route::post('loans/{loan}/repay', [App\Http\Controllers\LoanController::class, 'repay'])->name('loans.repay');
    
    // Payment Schedule routes
    Route::get('loans/{loan}/payment-schedule', [App\Http\Controllers\LoanController::class, 'paymentSchedule'])->name('loans.payment-schedule');
    Route::post('loans/{loan}/generate-schedule', [App\Http\Controllers\LoanController::class, 'generateSchedule'])->name('loans.generate-schedule');
    Route::get('loans/{loan}/print-schedule', [App\Http\Controllers\LoanController::class, 'printSchedule'])->name('loans.print-schedule');
    
    // Loan Repayment routes
    Route::get('loans/{loan}/repayment', [App\Http\Controllers\LoanController::class, 'repaymentForm'])->name('loans.repayment');
    Route::post('loans/{loan}/process-repayment', [App\Http\Controllers\LoanController::class, 'processRepayment'])->name('loans.process-repayment');
    
    Route::resource('loan-applications', App\Http\Controllers\LoanApplicationController::class);
    
    // Transaction Management - All authenticated users
    Route::resource('transactions', App\Http\Controllers\TransactionController::class);
    Route::post('transactions/{transaction}/approve', [App\Http\Controllers\TransactionController::class, 'approve'])->name('transactions.approve');
    Route::post('transactions/{transaction}/reject', [App\Http\Controllers\TransactionController::class, 'reject'])->name('transactions.reject');
    Route::post('transactions/{transaction}/reverse', [App\Http\Controllers\TransactionController::class, 'reverse'])->name('transactions.reverse');
    
    // User Management - Admin only
    Route::resource('users', App\Http\Controllers\UsersController::class)
        ->middleware('role:admin');
    
    // Accounting Routes - Include the accounting module
    require __DIR__.'/accounting.php';
    
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
    Route::prefix('borrower')->name('borrower.')->middleware('role:borrower')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\BorrowerController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/realtime', [App\Http\Controllers\BorrowerController::class, 'getRealtimeData'])->name('dashboard.realtime');
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
        
        // Reports
        Route::get('/reports/financial', [App\Http\Controllers\BorrowerReportController::class, 'financial'])->name('reports.financial');
    });
});

require __DIR__.'/auth.php';
