@props(['role' => 'admin'])

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="fas fa-university"></i>
            <span class="brand-text">Microbook-G5</span>
        </div>
        <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="sidebar-user">
        <div class="user-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="user-info">
            <div class="user-name">{{ auth()->user()->name }}</div>
            <div class="user-role">{{ ucfirst($role) }}</div>
        </div>
    </div>

    @php
        // Get real-time financial data for sidebar
        try {
            $analytics = app(\App\Services\FinancialAnalyticsService::class)->getComprehensiveAnalytics(
                $role === 'branch_manager' ? auth()->user()->branch_id : null,
                $role === 'loan_officer' ? auth()->id() : null
            );
        } catch (\Exception $e) {
            $analytics = [
                'loans_due_today' => ['count' => 0, 'amount' => 0],
                'overdue_loans' => ['count' => 0, 'amount' => 0, 'percentage' => 0],
                'active_loans' => ['count' => 0, 'amount' => 0, 'outstanding' => 0],
                'loan_requests' => ['count' => 0, 'amount' => 0],
                'released_principal' => ['total' => 0, 'this_month' => 0],
                'outstanding_principal' => ['total' => 0],
                'interest_collected' => ['total' => 0, 'this_month' => 0],
                'repayments_collected' => ['total' => 0, 'this_month' => 0],
                'active_borrowers' => ['count' => 0, 'percentage' => 0],
                'portfolio_at_risk' => [
                    '14_day_par' => ['percentage' => 0],
                    '30_day_par' => ['percentage' => 0],
                    'total_par' => ['percentage' => 0]
                ]
            ];
        }
    @endphp

    <!-- Real-time Financial Metrics Sidebar (Admin & Branch Manager Only) -->
    @if($role !== 'loan_officer' && $role !== 'borrower')
    <div class="sidebar-financial-metrics">
        <div class="metrics-header">
            <h6><i class="fas fa-chart-line me-2"></i>Live Financial Metrics</h6>
            <small class="text-muted" id="last-update">Updated: {{ now()->format('H:i:s') }}</small>
        </div>
        
        <!-- Key Performance Indicators -->
        <div class="metrics-section">
            <h6 class="metrics-title">Portfolio Overview</h6>
            <div class="metric-item">
                <div class="metric-icon bg-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="loans-due-today">{{ $analytics['loans_due_today']['count'] }}</div>
                    <div class="metric-label">Due Today</div>
                    <div class="metric-amount">${{ number_format($analytics['loans_due_today']['amount'], 0) }}</div>
                </div>
            </div>
            
            <div class="metric-item">
                <div class="metric-icon bg-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="overdue-loans">{{ $analytics['overdue_loans']['count'] }}</div>
                    <div class="metric-label">Overdue</div>
                    <div class="metric-amount">${{ number_format($analytics['overdue_loans']['amount'], 0) }}</div>
                </div>
            </div>
            
            <div class="metric-item">
                <div class="metric-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="active-loans">{{ $analytics['active_loans']['count'] }}</div>
                    <div class="metric-label">Active Loans</div>
                    <div class="metric-amount">${{ number_format($analytics['active_loans']['outstanding'], 0) }}</div>
                </div>
            </div>
            
            <div class="metric-item">
                <div class="metric-icon bg-info">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="loan-requests">{{ $analytics['loan_requests']['count'] }}</div>
                    <div class="metric-label">Requests</div>
                    <div class="metric-amount">${{ number_format($analytics['loan_requests']['amount'], 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Financial Performance -->
        <div class="metrics-section">
            <h6 class="metrics-title">Financial Performance</h6>
            <div class="metric-item">
                <div class="metric-icon bg-primary">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="released-principal">${{ number_format($analytics['released_principal']['total'] / 1000, 0) }}K</div>
                    <div class="metric-label">Released Principal</div>
                    <div class="metric-sub">This Month: ${{ number_format($analytics['released_principal']['this_month'] / 1000, 0) }}K</div>
                </div>
            </div>
            
            <div class="metric-item">
                <div class="metric-icon bg-warning">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="outstanding-principal">${{ number_format($analytics['outstanding_principal']['total'] / 1000, 0) }}K</div>
                    <div class="metric-label">Outstanding</div>
                    <div class="metric-sub">Active Portfolio</div>
                </div>
            </div>
            
            <div class="metric-item">
                <div class="metric-icon bg-success">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="interest-collected">${{ number_format($analytics['interest_collected']['total'] / 1000, 0) }}K</div>
                    <div class="metric-label">Interest Collected</div>
                    <div class="metric-sub">This Month: ${{ number_format($analytics['interest_collected']['this_month'] / 1000, 0) }}K</div>
                </div>
            </div>
            
            <div class="metric-item">
                <div class="metric-icon bg-info">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="repayments-collected">${{ number_format($analytics['repayments_collected']['total'] / 1000, 0) }}K</div>
                    <div class="metric-label">Repayments</div>
                    <div class="metric-sub">This Month: ${{ number_format($analytics['repayments_collected']['this_month'] / 1000, 0) }}K</div>
                </div>
            </div>
        </div>

        <!-- Portfolio at Risk -->
        <div class="metrics-section">
            <h6 class="metrics-title">Portfolio at Risk</h6>
            <div class="metric-item">
                <div class="metric-icon bg-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="par-14">{{ number_format($analytics['portfolio_at_risk']['14_day_par']['percentage'], 1) }}%</div>
                    <div class="metric-label">14-Day PAR</div>
                    <div class="metric-sub">Early Risk</div>
                </div>
            </div>
            
            <div class="metric-item">
                <div class="metric-icon bg-danger">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="par-30">{{ number_format($analytics['portfolio_at_risk']['30_day_par']['percentage'], 1) }}%</div>
                    <div class="metric-label">30-Day PAR</div>
                    <div class="metric-sub">Medium Risk</div>
                </div>
            </div>
            
            <div class="metric-item">
                <div class="metric-icon bg-danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="par-total">{{ number_format($analytics['portfolio_at_risk']['total_par']['percentage'], 1) }}%</div>
                    <div class="metric-label">Total PAR</div>
                    <div class="metric-sub">Overall Risk</div>
                </div>
            </div>
        </div>

        <!-- Active Borrowers -->
        <div class="metrics-section">
            <h6 class="metrics-title">Client Base</h6>
            <div class="metric-item">
                <div class="metric-icon bg-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="active-borrowers">{{ $analytics['active_borrowers']['count'] }}</div>
                    <div class="metric-label">Active Borrowers</div>
                    <div class="metric-sub">{{ number_format($analytics['active_borrowers']['percentage'], 1) }}% of total</div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Main Dashboard</span>
                </a>
            </li>
            
            @if($role === 'admin')
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-crown"></i>
                    <span class="nav-text">Admin Dashboard</span>
                </a>
            </li>
            @endif
            
            @if($role === 'branch_manager')
            <li class="nav-item">
                <a href="{{ route('branch-manager.dashboard') }}" class="nav-link {{ request()->routeIs('branch-manager.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-building"></i>
                    <span class="nav-text">Branch Dashboard</span>
                </a>
            </li>
            @endif
            
            @if($role === 'loan_officer')
            <li class="nav-item">
                <a href="{{ route('loan-officer.dashboard') }}" class="nav-link {{ request()->routeIs('loan-officer.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-user-tie"></i>
                    <span class="nav-text">My Dashboard</span>
                </a>
            </li>
            @endif
            
                @if($role === 'admin')
                    <!-- Admin Menu - Complete System Access -->
                    
                    <!-- System Management -->
                    <li class="nav-section">
                        <span class="nav-section-title">System Management</span>
                    </li>
                    @can('manage-users')
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="fas fa-users-cog"></i>
                            <span class="nav-text">User Management</span>
                        </a>
                    </li>
                    @endcan
                    @can('manage-branches')
                    <li class="nav-item">
                        <a href="{{ route('branches.index') }}" class="nav-link {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                            <i class="fas fa-building"></i>
                            <span class="nav-text">Branch Management</span>
                        </a>
                    </li>
                    @endcan
                    @can('manage-settings')
                    <li class="nav-item">
                        <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <i class="fas fa-cog"></i>
                            <span class="nav-text">System Settings</span>
                        </a>
                    </li>
                    @endcan
                    @can('view-audit-logs')
                    <li class="nav-item">
                        <a href="{{ route('audit-logs.index') }}" class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                            <i class="fas fa-history"></i>
                            <span class="nav-text">Audit Logs</span>
                        </a>
                    </li>
                    @endcan
                    @can('manage-backup')
                    <li class="nav-item">
                        <a href="{{ route('backup.create') }}" class="nav-link {{ request()->routeIs('backup.*') ? 'active' : '' }}">
                            <i class="fas fa-database"></i>
                            <span class="nav-text">Backup & Restore</span>
                        </a>
                    </li>
                    @endcan
                
                <!-- Client Management -->
                <li class="nav-section">
                    <span class="nav-section-title">Client Management</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">All Clients</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('kyc-documents.index') }}" class="nav-link {{ request()->routeIs('kyc-documents.*') ? 'active' : '' }}">
                        <i class="fas fa-id-card"></i>
                        <span class="nav-text">KYC Documents</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('client-risk-profiles.index') }}" class="nav-link {{ request()->routeIs('client-risk-profiles.*') ? 'active' : '' }}">
                        <i class="fas fa-shield-alt"></i>
                        <span class="nav-text">Risk Profiles</span>
                    </a>
                </li>
                
                <!-- Loan Management -->
                <li class="nav-section">
                    <span class="nav-section-title">Loan Management</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('loans.index') }}" class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span class="nav-text">All Loans</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('loan-applications.index') }}" class="nav-link {{ request()->routeIs('loan-applications.*') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i>
                        <span class="nav-text">Loan Applications</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('loan-repayments.index') }}" class="nav-link {{ request()->routeIs('loan-repayments.*') ? 'active' : '' }}">
                        <i class="fas fa-money-check-alt"></i>
                        <span class="nav-text">Loan Repayments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('collaterals.index') }}" class="nav-link {{ request()->routeIs('collaterals.*') ? 'active' : '' }}">
                        <i class="fas fa-gem"></i>
                        <span class="nav-text">Collaterals</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('approval-workflows.index') }}" class="nav-link {{ request()->routeIs('approval-workflows.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-check"></i>
                        <span class="nav-text">Approval Workflows</span>
                    </a>
                </li>
                
                <!-- Savings Management -->
                <li class="nav-section">
                    <span class="nav-section-title">Savings Management</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('savings-accounts.index') }}" class="nav-link {{ request()->routeIs('savings-accounts.*') ? 'active' : '' }}">
                        <i class="fas fa-piggy-bank"></i>
                        <span class="nav-text">Savings Accounts</span>
                    </a>
                </li>
                
                <!-- Transaction Management -->
                <li class="nav-section">
                    <span class="nav-section-title">Transaction Management</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt"></i>
                        <span class="nav-text">All Transactions</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="nav-text">Payments</span>
                    </a>
                </li>
                
                <!-- Collections & Recovery -->
                <li class="nav-section">
                    <span class="nav-section-title">Collections & Recovery</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('collections.index') }}" class="nav-link {{ request()->routeIs('collections.*') ? 'active' : '' }}">
                        <i class="fas fa-credit-card"></i>
                        <span class="nav-text">Collections</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('recovery-actions.index') }}" class="nav-link {{ request()->routeIs('recovery-actions.*') ? 'active' : '' }}">
                        <i class="fas fa-tools"></i>
                        <span class="nav-text">Recovery Actions</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('communication-logs.index') }}" class="nav-link {{ request()->routeIs('communication-logs.*') ? 'active' : '' }}">
                        <i class="fas fa-comments"></i>
                        <span class="nav-text">Communication Logs</span>
                    </a>
                </li>
                
                <!-- Human Resources -->
                <li class="nav-section">
                    <span class="nav-section-title">Human Resources</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('staff.index') }}" class="nav-link {{ request()->routeIs('staff.*') ? 'active' : '' }}">
                        <i class="fas fa-user-tie"></i>
                        <span class="nav-text">Staff Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('payrolls.index') }}" class="nav-link {{ request()->routeIs('payrolls.*') ? 'active' : '' }}">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="nav-text">Payroll Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                        <i class="fas fa-clock"></i>
                        <span class="nav-text">Attendance</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('performance.index') }}" class="nav-link {{ request()->routeIs('performance.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span class="nav-text">Performance</span>
                    </a>
                </li>
                
                <!-- Microbook-G5 Accounting Module - Real-Time Financial Data -->
                <li class="nav-section">
                    <span class="nav-section-title">Microbook-G5 Accounting</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('accounting.dashboard') }}" class="nav-link {{ request()->routeIs('accounting.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="nav-text">Accounting Dashboard</span>
                        <span class="badge bg-success badge-sm">Live</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('accounting.chart-of-accounts') }}" class="nav-link {{ request()->routeIs('accounting.chart-of-accounts*') ? 'active' : '' }}">
                        <i class="fas fa-list"></i>
                        <span class="nav-text">Chart of Accounts</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('accounting.general-ledger') }}" class="nav-link {{ request()->routeIs('accounting.general-ledger*') ? 'active' : '' }}">
                        <i class="fas fa-book"></i>
                        <span class="nav-text">General Ledger</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('accounting.journal-entries') }}" class="nav-link {{ request()->routeIs('accounting.journal-entries*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice"></i>
                        <span class="nav-text">Journal Entries</span>
                    </a>
                </li>
                
                <!-- Revenue & Income -->
                <li class="nav-section">
                    <span class="nav-section-title">Revenue & Income</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('accounting.revenues.index') }}" class="nav-link {{ request()->routeIs('accounting.revenues.*') ? 'active' : '' }}">
                        <i class="fas fa-coins"></i>
                        <span class="nav-text">Revenue Entries</span>
                    </a>
                </li>
                
                <!-- Expenses & Costs -->
                <li class="nav-section">
                    <span class="nav-section-title">Expenses & Costs</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('accounting.expense-entries') }}" class="nav-link {{ request()->routeIs('accounting.expense-entries*') ? 'active' : '' }}">
                        <i class="fas fa-receipt"></i>
                        <span class="nav-text">Expense Entries</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('accounting.expenses.index') }}" class="nav-link {{ request()->routeIs('accounting.expenses.*') ? 'active' : '' }}">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="nav-text">Expenses</span>
                    </a>
                </li>
                
                <!-- Banking & Transfers -->
                <li class="nav-section">
                    <span class="nav-section-title">Banking & Transfers</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('accounting.banks.index') }}" class="nav-link {{ request()->routeIs('accounting.banks.*') ? 'active' : '' }}">
                        <i class="fas fa-university"></i>
                        <span class="nav-text">Banks</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('accounting.transfers.index') }}" class="nav-link {{ request()->routeIs('accounting.transfers.*') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt"></i>
                        <span class="nav-text">Transfers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('accounting.reconciliations') }}" class="nav-link {{ request()->routeIs('accounting.reconciliations*') ? 'active' : '' }}">
                        <i class="fas fa-balance-scale"></i>
                        <span class="nav-text">Reconciliations</span>
                    </a>
                </li>
                
                <!-- Reports & Analysis -->
                <li class="nav-section">
                    <span class="nav-section-title">Financial Reports</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('accounting.reports') }}" class="nav-link {{ request()->routeIs('accounting.reports*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span class="nav-text">Financial Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('accounting.audit-trail') }}" class="nav-link {{ request()->routeIs('accounting.audit-trail*') ? 'active' : '' }}">
                        <i class="fas fa-history"></i>
                        <span class="nav-text">Audit Trail</span>
                    </a>
                </li>
                
                <!-- Reports & Analytics -->
                <li class="nav-section">
                    <span class="nav-section-title">Reports & Analytics</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span class="nav-text">All Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reports.portfolio') }}" class="nav-link {{ request()->routeIs('reports.portfolio') ? 'active' : '' }}">
                        <i class="fas fa-chart-pie"></i>
                        <span class="nav-text">Portfolio Report</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reports.collections') }}" class="nav-link {{ request()->routeIs('reports.collections') ? 'active' : '' }}">
                        <i class="fas fa-credit-card"></i>
                        <span class="nav-text">Collections Report</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reports.performance') }}" class="nav-link {{ request()->routeIs('reports.performance') ? 'active' : '' }}">
                        <i class="fas fa-trophy"></i>
                        <span class="nav-text">Performance Report</span>
                    </a>
                </li>
                
                <!-- System Monitoring -->
                <li class="nav-section">
                    <span class="nav-section-title">System Monitoring</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('system-health.index') }}" class="nav-link {{ request()->routeIs('system-health.*') ? 'active' : '' }}">
                        <i class="fas fa-heartbeat"></i>
                        <span class="nav-text">System Health</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('logs.index') }}" class="nav-link {{ request()->routeIs('logs.*') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i>
                        <span class="nav-text">System Logs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('maintenance.index') }}" class="nav-link {{ request()->routeIs('maintenance.*') ? 'active' : '' }}">
                        <i class="fas fa-wrench"></i>
                        <span class="nav-text">Maintenance</span>
                    </a>
                </li>
                
            @elseif($role === 'general_manager')
                <!-- General Manager Menu -->
                <li class="nav-section">
                    <span class="nav-section-title">Overview & Management</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('branches.index') }}" class="nav-link {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                        <i class="fas fa-building"></i>
                        <span class="nav-text">Branch Overview</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">All Clients</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('loans.index') }}" class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span class="nav-text">All Loans</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('loan-applications.index') }}" class="nav-link {{ request()->routeIs('loan-applications.*') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i>
                        <span class="nav-text">Loan Applications</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('collections.index') }}" class="nav-link {{ request()->routeIs('collections.*') ? 'active' : '' }}">
                        <i class="fas fa-credit-card"></i>
                        <span class="nav-text">Collections Overview</span>
                    </a>
                </li>
                
            @elseif($role === 'branch_manager')
                <!-- Branch Manager Menu -->
                <li class="nav-section">
                    <span class="nav-section-title">Branch Operations</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">Client Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('loan-applications.index') }}" class="nav-link {{ request()->routeIs('loan-applications.*') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i>
                        <span class="nav-text">Loan Applications</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('loans.index') }}" class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span class="nav-text">Loan Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('savings-accounts.index') }}" class="nav-link {{ request()->routeIs('savings-accounts.*') ? 'active' : '' }}">
                        <i class="fas fa-piggy-bank"></i>
                        <span class="nav-text">Savings Accounts</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt"></i>
                        <span class="nav-text">Transactions</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('collections.index') }}" class="nav-link {{ request()->routeIs('collections.*') ? 'active' : '' }}">
                        <i class="fas fa-credit-card"></i>
                        <span class="nav-text">Collections</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="nav-text">Payments</span>
                    </a>
                </li>
                
            @elseif($role === 'loan_officer')
                <!-- Loan Officer Menu -->
                <li class="nav-section">
                    <span class="nav-section-title">Client Management</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">My Clients</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('kyc-documents.index') }}" class="nav-link {{ request()->routeIs('kyc-documents.*') ? 'active' : '' }}">
                        <i class="fas fa-id-card"></i>
                        <span class="nav-text">KYC Documents</span>
                    </a>
                </li>
                
                <li class="nav-section">
                    <span class="nav-section-title">Loan Operations</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('loan-applications.index') }}" class="nav-link {{ request()->routeIs('loan-applications.*') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i>
                        <span class="nav-text">Loan Applications</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('loans.index') }}" class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span class="nav-text">My Loans</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('collaterals.index') }}" class="nav-link {{ request()->routeIs('collaterals.*') ? 'active' : '' }}">
                        <i class="fas fa-gem"></i>
                        <span class="nav-text">Collaterals</span>
                    </a>
                </li>
                
                <li class="nav-section">
                    <span class="nav-section-title">Collections</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('loan-repayments.index') }}" class="nav-link {{ request()->routeIs('loan-repayments.*') ? 'active' : '' }}">
                        <i class="fas fa-money-check-alt"></i>
                        <span class="nav-text">Loan Repayments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('collections.index') }}" class="nav-link {{ request()->routeIs('collections.*') ? 'active' : '' }}">
                        <i class="fas fa-credit-card"></i>
                        <span class="nav-text">Collections</span>
                    </a>
                </li>
                
            @elseif($role === 'hr')
                <!-- HR Menu -->
                <li class="nav-section">
                    <span class="nav-section-title">Human Resources</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('staff.index') }}" class="nav-link {{ request()->routeIs('staff.*') ? 'active' : '' }}">
                        <i class="fas fa-user-tie"></i>
                        <span class="nav-text">Staff Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('payrolls.index') }}" class="nav-link {{ request()->routeIs('payrolls.*') ? 'active' : '' }}">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="nav-text">Payroll Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fas fa-users-cog"></i>
                        <span class="nav-text">User Accounts</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                        <i class="fas fa-clock"></i>
                        <span class="nav-text">Attendance</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('performance.index') }}" class="nav-link {{ request()->routeIs('performance.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span class="nav-text">Performance</span>
                    </a>
                </li>
                
            @elseif($role === 'borrower')
                <!-- Borrower Menu -->
                <li class="nav-section">
                    <span class="nav-section-title">My Account</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('borrower.dashboard') }}" class="nav-link {{ request()->routeIs('borrower.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="nav-text">My Dashboard</span>
                    </a>
                </li>
                
                <!-- Loans & Payments Section -->
                <li class="nav-section">
                    <span class="nav-section-title">Loans & Payments</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('borrower.loans.index') }}" class="nav-link {{ request()->routeIs('borrower.loans.*') ? 'active' : '' }}">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span class="nav-text">My Loans</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('borrower.loans.create') }}" class="nav-link {{ request()->routeIs('borrower.loans.create') ? 'active' : '' }}">
                        <i class="fas fa-plus-circle"></i>
                        <span class="nav-text">Apply for Loan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('borrower.payments.create') }}" class="nav-link {{ request()->routeIs('borrower.payments.*') ? 'active' : '' }}">
                        <i class="fas fa-credit-card"></i>
                        <span class="nav-text">Make Payment</span>
                    </a>
                </li>
                
                <!-- Savings Section -->
                <li class="nav-section">
                    <span class="nav-section-title">Savings</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('borrower.savings.index') }}" class="nav-link {{ request()->routeIs('borrower.savings.*') ? 'active' : '' }}">
                        <i class="fas fa-piggy-bank"></i>
                        <span class="nav-text">My Savings</span>
                    </a>
                </li>
                
                <!-- Transaction History -->
                <li class="nav-section">
                    <span class="nav-section-title">History</span>
                </li>
                <li class="nav-item">
                    <a href="{{ route('borrower.transactions.index') }}" class="nav-link {{ request()->routeIs('borrower.transactions.*') ? 'active' : '' }}">
                        <i class="fas fa-history"></i>
                        <span class="nav-text">Transaction History</span>
                    </a>
                </li>
            @endif
            
            <!-- Common Menu Items -->
            <li class="nav-section">
                <span class="nav-section-title">General</span>
            </li>
            <li class="nav-item">
                <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <i class="fas fa-bell"></i>
                    <span class="nav-text">Notifications</span>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="badge bg-danger">{{ auth()->user()->unreadNotifications->count() }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="fas fa-user"></i>
                    <span class="nav-text">Profile</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-text">Reports</span>
                </a>
            </li>
            
            <!-- Logout -->
            <li class="nav-item logout-item">
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="nav-link logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="nav-text">Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </nav>
</div>

<style>
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 280px;
        background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
        color: white;
        z-index: 1000;
        transition: transform 0.3s ease;
        overflow-y: auto;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    
    .sidebar.collapsed {
        transform: translateX(-100%);
    }
    
    .sidebar-header {
        padding: 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .sidebar-brand {
        display: flex;
        align-items: center;
        font-size: 1.25rem;
        font-weight: 700;
    }
    
    .sidebar-brand i {
        font-size: 1.5rem;
        margin-right: 0.75rem;
        color: #3498db;
    }
    
    .brand-text {
        white-space: nowrap;
    }
    
    .sidebar-toggle {
        background: none;
        border: none;
        color: white;
        font-size: 1.25rem;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }
    
    .sidebar-toggle:hover {
        background: rgba(255,255,255,0.1);
    }
    
    .sidebar-user {
        padding: 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        display: flex;
        align-items: center;
    }
    
    .user-avatar {
        font-size: 2.5rem;
        margin-right: 1rem;
        color: #3498db;
    }
    
    .user-name {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }
    
    .user-role {
        font-size: 0.875rem;
        color: #bdc3c7;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Quick Stats */
    .sidebar-quick-stats {
        padding: 1rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }

    .quick-stat-item {
        background: rgba(255,255,255,0.05);
        border-radius: 8px;
        padding: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .quick-stat-item:hover {
        background: rgba(255,255,255,0.1);
        transform: translateY(-2px);
    }

    .quick-stat-icon {
        width: 35px;
        height: 35px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .quick-stat-icon i {
        color: white;
    }

    .quick-stat-info {
        flex: 1;
        min-width: 0;
    }

    .quick-stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.25rem;
        color: white;
    }

    .quick-stat-label {
        font-size: 0.7rem;
        color: #bdc3c7;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .sidebar-nav {
        padding: 1rem 0;
    }
    
    .nav-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .nav-section {
        padding: 0.75rem 1.5rem 0.5rem;
    }
    
    .nav-section-title {
        font-size: 0.75rem;
        font-weight: 600;
        color: #95a5a6;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .nav-item {
        margin: 0.25rem 0;
    }
    
    .nav-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        color: #ecf0f1;
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .nav-link:hover {
        background: rgba(255,255,255,0.1);
        color: white;
        text-decoration: none;
    }
    
    .nav-link.active {
        background: rgba(52, 152, 219, 0.2);
        color: #3498db;
        border-right: 3px solid #3498db;
    }
    
    .nav-link i {
        width: 20px;
        margin-right: 0.75rem;
        text-align: center;
    }
    
    .nav-text {
        flex: 1;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 10px;
    }
    
    .logout-item {
        margin-top: 1rem;
        border-top: 1px solid rgba(255,255,255,0.1);
        padding-top: 1rem;
    }
    
    .logout-btn {
        background: none;
        border: none;
        width: 100%;
        text-align: left;
        color: #e74c3c;
    }
    
    .logout-btn:hover {
        background: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
    }
    
    /* Mobile Responsive */
    @media (max-width: 991.98px) {
        .sidebar {
            transform: translateX(-100%);
        }
        
        .sidebar.show {
            transform: translateX(0);
        }
        
        .sidebar-toggle {
            display: block;
        }
    }
    
    @media (min-width: 992px) {
        .sidebar-toggle {
            display: none;
        }
    }
    
    /* Scrollbar Styling */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }
    
    .sidebar::-webkit-scrollbar-track {
        background: rgba(255,255,255,0.1);
    }
    
    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.3);
        border-radius: 3px;
    }
    
    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255,255,255,0.5);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 992) {
                if (!sidebar.contains(e.target) && !e.target.closest('.sidebar-toggle')) {
                    sidebar.classList.remove('show');
                }
            }
        });
    });
</script>
