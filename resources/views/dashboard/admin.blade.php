@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

    <!-- Dashboard Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
                <div class="text-muted">
                    <i class="fas fa-calendar me-1"></i>
                    {{ now()->format('l, F j, Y') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Critical Loan Metrics -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value">{{ $stats['loans_due_today'] ?? 0 }}</div>
                    <div class="stat-label">Loans Due Today</div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value">{{ $stats['overdue_loans'] ?? 0 }}</div>
                    <div class="stat-label">Overdue Loans</div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card bg-success text-white h-100">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value">{{ $stats['active_loans_count'] ?? 0 }}</div>
                    <div class="stat-label">Active Loans</div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card bg-info text-white h-100">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-value">{{ $stats['pending_loans'] ?? 0 }}</div>
                    <div class="stat-label">Loan Requests</div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value">{{ $stats['active_borrowers'] ?? 0 }}</div>
                    <div class="stat-label">Active Borrowers</div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card bg-dark text-white h-100">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-value">{{ number_format($stats['default_rate'] ?? 0, 1) }}%</div>
                    <div class="stat-label">Default Rate</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['released_principal'] ?? 0, 0) }}</div>
                    <div class="stat-label">Released Principal</div>
                    <div class="mt-2">
                        <small class="opacity-75">Total Disbursed</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-gradient-warning text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['outstanding_principal'] ?? 0, 0) }}</div>
                    <div class="stat-label">Outstanding Principal</div>
                    <div class="mt-2">
                        <small class="opacity-75">To Be Collected</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-gradient-success text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['interest_collected'] ?? 0, 0) }}</div>
                    <div class="stat-label">Interest Collected</div>
                    <div class="mt-2">
                        <small class="opacity-75">This Period</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-gradient-info text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['realized_profit'] ?? 0, 0) }}</div>
                    <div class="stat-label">Realized Profit</div>
                    <div class="mt-2">
                        <small class="opacity-75">Expected: ${{ number_format($stats['expected_profit'] ?? 0, 0) }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Portfolio At Risk Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-danger text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['par_14_days'] ?? 0, 0) }}</div>
                    <div class="stat-label">14-Day PAR</div>
                    <div class="mt-2">
                        <small class="opacity-75">{{ number_format($stats['par_14_days_rate'] ?? 0, 2) }}%</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['par_30_days'] ?? 0, 0) }}</div>
                    <div class="stat-label">30-Day PAR</div>
                    <div class="mt-2">
                        <small class="opacity-75">{{ number_format($stats['par_30_days_rate'] ?? 0, 2) }}%</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-dark text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['par_over_30_days'] ?? 0, 0) }}</div>
                    <div class="stat-label">Over 30 Days PAR</div>
                    <div class="mt-2">
                        <small class="opacity-75">{{ number_format($stats['par_over_30_days_rate'] ?? 0, 2) }}%</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-secondary text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['total_par'] ?? 0, 0) }}</div>
                    <div class="stat-label">Total PAR</div>
                    <div class="mt-2">
                        <small class="opacity-75">{{ number_format($stats['par_percentage'] ?? 0, 2) }}%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Collections & Fees -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-gradient-success text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['repayments_collected'] ?? 0, 0) }}</div>
                    <div class="stat-label">Repayments Collected</div>
                    <div class="mt-2">
                        <small class="opacity-75">This Period</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-gradient-info text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['charged_fees'] ?? 0, 0) }}</div>
                    <div class="stat-label">Charged Fees</div>
                    <div class="mt-2">
                        <small class="opacity-75">Processing & Service</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-gradient-warning text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['penalties_collected'] ?? 0, 0) }}</div>
                    <div class="stat-label">Penalties Collected</div>
                    <div class="mt-2">
                        <small class="opacity-75">Late Payments</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['average_loan_size'] ?? 0, 0) }}</div>
                    <div class="stat-label">Average Loan Size</div>
                    <div class="mt-2">
                        <small class="opacity-75">Per Borrower</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value">{{ $stats['total_users'] ?? 0 }}</div>
                    <div class="stat-label">Total Users</div>
                    <div class="mt-2">
                        <small class="opacity-75">Active: {{ $stats['active_users'] ?? 0 }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-value">{{ $stats['total_branches'] ?? 0 }}</div>
                    <div class="stat-label">Branches</div>
                    <div class="mt-2">
                        <small class="opacity-75">Active: {{ $stats['active_branches'] ?? 0 }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['total_loan_portfolio'] ?? 0, 2) }}</div>
                    <div class="stat-label">Loan Portfolio</div>
                    <div class="mt-2">
                        <small class="opacity-75">Total Value</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['total_savings'] ?? 0, 2) }}</div>
                    <div class="stat-label">Total Savings</div>
                    <div class="mt-2">
                        <small class="opacity-75">Accounts: {{ $stats['savings_accounts'] ?? 0 }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loan Status Overview -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i>Loans Released vs Completed vs Defaulted
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-md-4">
                            <div class="loan-status-metric">
                                <i class="fas fa-paper-plane fa-3x text-primary mb-3"></i>
                                <h3 class="text-primary">{{ $stats['loans_released'] ?? 0 }}</h3>
                                <p class="text-muted">Loans Released</p>
                                <small class="text-muted">${{ number_format($stats['loans_released_amount'] ?? 0, 0) }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="loan-status-metric">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h3 class="text-success">{{ $stats['loans_completed'] ?? 0 }}</h3>
                                <p class="text-muted">Loans Completed</p>
                                <small class="text-muted">${{ number_format($stats['loans_completed_amount'] ?? 0, 0) }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="loan-status-metric">
                                <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                                <h3 class="text-danger">{{ $stats['loans_defaulted'] ?? 0 }}</h3>
                                <p class="text-muted">Loans Defaulted</p>
                                <small class="text-muted">${{ number_format($stats['loans_defaulted_amount'] ?? 0, 0) }}</small>
                            </div>
                        </div>
                    </div>
                    <canvas id="loanStatusComparisonChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status & Quick Actions -->
    <div class="row mb-4">
        <!-- System Status -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-server me-2"></i>System Status
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="status-indicator bg-success me-3"></div>
                                <div>
                                    <div class="fw-bold">Database</div>
                                    <small class="text-muted">Connected</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="status-indicator bg-success me-3"></div>
                                <div>
                                    <div class="fw-bold">Cache</div>
                                    <small class="text-muted">Active</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="status-indicator bg-warning me-3"></div>
                                <div>
                                    <div class="fw-bold">Queue</div>
                                    <small class="text-muted">{{ $stats['pending_jobs'] ?? 0 }} pending</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="status-indicator bg-success me-3"></div>
                                <div>
                                    <div class="fw-bold">Storage</div>
                                    <small class="text-muted">{{ $stats['storage_usage'] ?? '0' }}% used</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="{{ route('users.create') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-user-plus me-2"></i>Add User
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('branches.create') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-building me-2"></i>Add Branch
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('clients.create') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-user-plus me-2"></i>New Client
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('loans.create') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-plus me-2"></i>New Loan
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('collections.index') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-credit-card me-2"></i>Collections
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('reports.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-chart-bar me-2"></i>Reports
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('backup.create') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-database me-2"></i>Backup System
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('settings.index') }}" class="btn btn-outline-dark w-100">
                                <i class="fas fa-cog me-2"></i>System Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Performance Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>System-Wide Performance Trends
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="systemPerformanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Performance Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i>Branch Performance Overview
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Branch</th>
                                    <th>Clients</th>
                                    <th>Active Loans</th>
                                    <th>Loan Portfolio</th>
                                    <th>Savings</th>
                                    <th>PAR %</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branchPerformance ?? [] as $branch)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-building text-primary me-2"></i>
                                                {{ $branch->name }}
                                            </div>
                                        </td>
                                        <td>{{ $branch->clients_count ?? 0 }}</td>
                                        <td>{{ $branch->active_loans_count ?? 0 }}</td>
                                        <td>${{ number_format($branch->loan_portfolio ?? 0, 2) }}</td>
                                        <td>${{ number_format($branch->savings_balance ?? 0, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ ($branch->par_percentage ?? 0) > 5 ? 'danger' : 'success' }}">
                                                {{ number_format($branch->par_percentage ?? 0, 2) }}%
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $branch->is_active ? 'success' : 'danger' }}">
                                                {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No branch data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loan Management Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-hand-holding-usd me-2"></i>Loan Management
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="loanTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="active-loans-tab" data-bs-toggle="tab" data-bs-target="#active-loans" type="button">
                                <i class="fas fa-check-circle me-1"></i>Active Loans ({{ \App\Models\Loan::where('status', 'active')->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="requested-loans-tab" data-bs-toggle="tab" data-bs-target="#requested-loans" type="button">
                                <i class="fas fa-clock me-1"></i>Requested Loans ({{ \App\Models\LoanApplication::where('status', 'pending')->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="all-loans-tab" data-bs-toggle="tab" data-bs-target="#all-loans" type="button">
                                <i class="fas fa-list me-1"></i>All Loans ({{ \App\Models\Loan::count() }})
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="loanTabsContent">
                        <!-- Active Loans -->
                        <div class="tab-pane fade show active" id="active-loans" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Loan #</th>
                                            <th>Client</th>
                                            <th>Amount</th>
                                            <th>Outstanding</th>
                                            <th>Next Payment</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse(\App\Models\Loan::where('status', 'active')->with('client')->latest()->take(10)->get() as $loan)
                                            <tr>
                                                <td><strong>{{ $loan->loan_number }}</strong></td>
                                                <td>{{ $loan->client->full_name }}</td>
                                                <td>${{ number_format($loan->amount, 2) }}</td>
                                                <td>${{ number_format($loan->outstanding_balance, 2) }}</td>
                                                <td>{{ $loan->getNextPaymentDue() ? $loan->getNextPaymentDue()->format('M d, Y') : 'N/A' }}</td>
                                                <td><span class="badge bg-success">Active</span></td>
                                                <td>
                                                    <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-3">No active loans</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="{{ route('loans.index') }}?status=active" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-right me-1"></i>View All Active Loans
                                </a>
                            </div>
                        </div>

                        <!-- Requested Loans -->
                        <div class="tab-pane fade" id="requested-loans" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Application #</th>
                                            <th>Client</th>
                                            <th>Requested Amount</th>
                                            <th>Term</th>
                                            <th>Purpose</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse(\App\Models\LoanApplication::where('status', 'pending')->with('client')->latest()->take(10)->get() as $app)
                                            <tr>
                                                <td><strong>{{ $app->application_number }}</strong></td>
                                                <td>{{ $app->client->full_name ?? 'N/A' }}</td>
                                                <td>${{ number_format($app->requested_amount, 2) }}</td>
                                                <td>{{ $app->term_months ?? $app->requested_term_months }} months</td>
                                                <td>{{ \Str::limit($app->purpose ?? $app->loan_purpose, 30) }}</td>
                                                <td>{{ $app->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <a href="{{ route('loan-applications.show', $app) }}" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-3">No pending loan requests</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="{{ route('loan-applications.index') }}?status=pending" class="btn btn-outline-warning">
                                    <i class="fas fa-arrow-right me-1"></i>View All Loan Requests
                                </a>
                            </div>
                        </div>

                        <!-- All Loans -->
                        <div class="tab-pane fade" id="all-loans" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Loan #</th>
                                            <th>Client</th>
                                            <th>Branch</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse(\App\Models\Loan::with('client', 'branch')->latest()->take(15)->get() as $loan)
                                            <tr>
                                                <td><strong>{{ $loan->loan_number }}</strong></td>
                                                <td>{{ $loan->client->full_name }}</td>
                                                <td>{{ $loan->branch->name ?? 'N/A' }}</td>
                                                <td>${{ number_format($loan->amount, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ 
                                                        $loan->status == 'active' ? 'success' : 
                                                        ($loan->status == 'pending' ? 'warning' : 
                                                        ($loan->status == 'overdue' ? 'danger' : 'info')) 
                                                    }}">
                                                        {{ ucfirst($loan->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $loan->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-3">No loans found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="{{ route('loans.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-right me-1"></i>View All Loans
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Borrower Management -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-users me-2"></i>Active Borrowers
                    </h6>
                    <a href="{{ route('clients.index') }}" class="btn btn-sm btn-success">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Client #</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Active Loans</th>
                                    <th>Total Borrowed</th>
                                    <th>KYC Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(\App\Models\Client::where('status', 'active')->with('loans')->latest()->take(10)->get() as $client)
                                    <tr>
                                        <td><strong>{{ $client->client_number }}</strong></td>
                                        <td>
                                            @if($client->avatar)
                                                <img src="{{ Storage::url($client->avatar) }}" alt="{{ $client->full_name }}" 
                                                     class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                            @endif
                                            {{ $client->full_name }}
                                        </td>
                                        <td>{{ $client->email }}</td>
                                        <td>{{ $client->phone }}</td>
                                        <td><span class="badge bg-primary">{{ $client->loans->where('status', 'active')->count() }}</span></td>
                                        <td>${{ number_format($client->loans->sum('amount'), 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $client->kyc_status == 'verified' ? 'success' : 'warning' }}">
                                                {{ ucfirst($client->kyc_status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-3">No active borrowers</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Loan Activities -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Recent Loan Activities
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Activity</th>
                                    <th>Client</th>
                                    <th>Branch</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($recentActivities) && is_array($recentActivities))
                                    @forelse($recentActivities as $activityType => $activities)
                                        @if(is_iterable($activities))
                                            @foreach($activities as $activity)
                                                <tr>
                                                    <td>{{ isset($activity->created_at) ? $activity->created_at->format('M d, Y H:i') : now()->format('M d, Y H:i') }}</td>
                                                    <td>
                                                        <i class="fas fa-{{ $activityType === 'loan_disbursements' ? 'hand-holding-usd' : ($activityType === 'loan_repayments' ? 'money-bill-wave' : 'bell') }} me-2"></i>
                                                        {{ $activity->description ?? $activity->event ?? ucfirst(str_replace('_', ' ', $activityType)) }}
                                                    </td>
                                                    <td>{{ $activity->client->full_name ?? $activity->causer->name ?? 'N/A' }}</td>
                                                    <td>{{ $activity->branch->name ?? $activity->client->branch->name ?? 'N/A' }}</td>
                                                    <td>${{ number_format($activity->amount ?? 0, 2) }}</td>
                                                    <td>
                                                        <span class="badge bg-success">Completed</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">No recent loan activities found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No recent loan activities found</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent System Activities -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Recent System Activities
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities['audit_logs'] ?? [] as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('M d, H:i') }}</td>
                                        <td>{{ $log->causer->name ?? 'System' }}</td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $log->event == 'created' ? 'success' : 
                                                ($log->event == 'updated' ? 'info' : 
                                                ($log->event == 'deleted' ? 'danger' : 'secondary')) 
                                            }}">
                                                {{ ucfirst($log->event ?? 'activity') }}
                                            </span>
                                        </td>
                                        <td>{{ $log->description }}</td>
                                        <td>
                                            @if(isset($log->properties['ip']))
                                                {{ $log->properties['ip'] }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No recent activities</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Alerts -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-exclamation-circle me-2"></i>System Alerts
                    </h6>
                </div>
                <div class="card-body">
                    @if(($stats['par_percentage'] ?? 0) > 5)
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>High PAR:</strong> Portfolio at Risk is {{ number_format($stats['par_percentage'], 2) }}%
                        </div>
                    @endif

                    @if(($stats['overdue_loans'] ?? 0) > 10)
                        <div class="alert alert-warning">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Overdue Loans:</strong> {{ $stats['overdue_loans'] }} loans are overdue
                        </div>
                    @endif

                    @if(($stats['pending_jobs'] ?? 0) > 50)
                        <div class="alert alert-info">
                            <i class="fas fa-tasks me-2"></i>
                            <strong>Queue Backlog:</strong> {{ $stats['pending_jobs'] }} jobs pending
                        </div>
                    @endif

                    @if(($stats['storage_usage'] ?? 0) > 80)
                        <div class="alert alert-warning">
                            <i class="fas fa-hdd me-2"></i>
                            <strong>Storage Warning:</strong> {{ $stats['storage_usage'] }}% disk usage
                        </div>
                    @endif

                    @if(($stats['par_percentage'] ?? 0) <= 5 && ($stats['overdue_loans'] ?? 0) <= 10)
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>All Systems Normal</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


    <!-- Financial Charts -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>Monthly Loan Disbursements
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="loanDisbursementsChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>Loan Status Distribution
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="loanStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Portfolio Health -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-shield-alt me-2"></i>Portfolio Health Overview
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="portfolio-metric">
                                <div class="metric-value text-success">{{ $stats['low_risk_loans'] ?? 0 }}</div>
                                <div class="metric-label">Low Risk</div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="portfolio-metric">
                                <div class="metric-value text-warning">{{ $stats['medium_risk_loans'] ?? 0 }}</div>
                                <div class="metric-label">Medium Risk</div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="portfolio-metric">
                                <div class="metric-value text-danger">{{ $stats['high_risk_loans'] ?? 0 }}</div>
                                <div class="metric-label">High Risk</div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="portfolio-metric">
                                <div class="metric-value text-dark">{{ $stats['defaulted_loans'] ?? 0 }}</div>
                                <div class="metric-label">Defaulted</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.portfolio-metric {
    padding: 1rem;
    border-radius: 8px;
    background: #f8f9fa;
    margin-bottom: 1rem;
}

.metric-value {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.metric-label {
    font-size: 0.9rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Gradient backgrounds for stat cards */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%);
}

/* Enhanced stat card styling */
.stat-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}

.stat-icon {
    font-size: 2.5rem;
    opacity: 0.8;
    float: right;
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    margin-top: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
    margin-top: 0.5rem;
}

/* Loan Status Metrics */
.loan-status-metric {
    padding: 1.5rem;
    transition: transform 0.3s ease;
}

.loan-status-metric:hover {
    transform: translateY(-5px);
}

.loan-status-metric h3 {
    font-weight: bold;
    margin: 0.5rem 0;
}

.loan-status-metric p {
    margin: 0.25rem 0;
    font-weight: 600;
}
</style>
@endsection

@section('scripts')
<script>
// System Performance Chart (Loans Disbursed & Collections)
const systemPerformanceCtx = document.getElementById('systemPerformanceChart').getContext('2d');
const systemPerformanceChart = new Chart(systemPerformanceCtx, {
    type: 'line',
    data: {
        labels: {!! isset($monthlyTrends) ? json_encode($monthlyTrends->pluck('month')) : json_encode(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) !!},
        datasets: [{
            label: 'Loans Disbursed',
            data: {!! isset($monthlyTrends) ? json_encode($monthlyTrends->pluck('loans_disbursed')) : json_encode([12, 19, 15, 25, 22, 30]) !!},
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            fill: true
        }, {
            label: 'Collections',
            data: {!! isset($monthlyTrends) ? json_encode($monthlyTrends->pluck('collections')) : json_encode([8, 15, 12, 20, 18, 25]) !!},
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += '$' + context.parsed.y.toLocaleString();
                        return label;
                    }
                }
            }
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        }
    }
});

// Loan Disbursements Chart
const loanDisbursementsCtx = document.getElementById('loanDisbursementsChart').getContext('2d');
const loanDisbursementsChart = new Chart(loanDisbursementsCtx, {
    type: 'line',
    data: {
        labels: {!! isset($monthlyTrends) ? json_encode($monthlyTrends->pluck('month')) : json_encode(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) !!},
        datasets: [{
            label: 'Loan Disbursements',
            data: {!! isset($monthlyTrends) ? json_encode($monthlyTrends->pluck('loans_disbursed')) : json_encode([12000, 19000, 15000, 25000, 22000, 30000]) !!},
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Loan Status Distribution Chart
const loanStatusCtx = document.getElementById('loanStatusChart').getContext('2d');
const loanStatusChart = new Chart(loanStatusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Active', 'Overdue', 'Completed', 'Defaulted'],
        datasets: [{
            data: [
                {{ $loanStats['active_loans'] ?? 0 }},
                {{ $loanStats['overdue_loans'] ?? 0 }},
                {{ $loanStats['completed_loans'] ?? 0 }},
                {{ $loanStats['defaulted_loans'] ?? 0 }}
            ],
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 159, 64, 0.8)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

// Loans Released vs Completed vs Defaulted Chart
const loanStatusComparisonCtx = document.getElementById('loanStatusComparisonChart').getContext('2d');
const loanStatusComparisonChart = new Chart(loanStatusComparisonCtx, {
    type: 'bar',
    data: {
        labels: ['Released', 'Completed', 'Defaulted'],
        datasets: [{
            label: 'Number of Loans',
            data: [
                {{ $stats['loans_released'] ?? 0 }},
                {{ $stats['loans_completed'] ?? 0 }},
                {{ $stats['loans_defaulted'] ?? 0 }}
            ],
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 2
        }, {
            label: 'Amount ($)',
            data: [
                {{ $stats['loans_released_amount'] ?? 0 }},
                {{ $stats['loans_completed_amount'] ?? 0 }},
                {{ $stats['loans_defaulted_amount'] ?? 0 }}
            ],
            backgroundColor: [
                'rgba(54, 162, 235, 0.5)',
                'rgba(75, 192, 192, 0.5)',
                'rgba(255, 99, 132, 0.5)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 2,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Loans'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Amount ($)'
                },
                grid: {
                    drawOnChartArea: false,
                },
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.dataset.label === 'Amount ($)') {
                            label += '$' + context.parsed.y.toLocaleString();
                        } else {
                            label += context.parsed.y;
                        }
                        return label;
                    }
                }
            }
        }
    }
});
</script>
@endsection