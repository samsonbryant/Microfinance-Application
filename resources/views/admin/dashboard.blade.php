@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-crown text-warning me-2"></i>Admin Dashboard
            </h1>
            <p class="text-muted mb-0">System-wide Overview & Analytics</p>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-info" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button type="button" class="btn btn-primary" onclick="exportData()">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Real-time Status Bar -->
    <div class="alert alert-info d-flex align-items-center justify-content-between mb-4">
        <div>
            <i class="fas fa-circle text-success me-2 pulse-animation"></i>
            <strong>Live Data:</strong> Last updated <span id="last-update-time">just now</span>
        </div>
        <small class="text-muted">Auto-refreshes every 30 seconds</small>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Key Performance Indicators - Standard Microfinance Order -->
    <div class="row g-3 mb-4">
        <!-- Portfolio at Risk (PAR) -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Portfolio at Risk</div>
                            <div class="stat-value" id="par-total">
                                {{ number_format($analytics['portfolio_at_risk']['total_par']['percentage'] ?? 0, 2) }}%
                            </div>
                            <div class="stat-change">
                                <i class="fas fa-exclamation-triangle"></i>
                                ${{ number_format(($analytics['portfolio_at_risk']['total_par']['amount'] ?? 0) / 1000, 1) }}K at Risk
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gross Loan Portfolio -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Gross Loan Portfolio</div>
                            <div class="stat-value" id="gross-portfolio">
                                ${{ number_format(($analytics['outstanding_principal']['total'] ?? 0) / 1000, 0) }}K
                            </div>
                            <div class="stat-change">
                                <i class="fas fa-chart-line"></i>
                                {{ number_format($analytics['active_loans']['count'] ?? 0) }} Active Loans
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-hand-holding-usd fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Borrowers -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Active Borrowers</div>
                            <div class="stat-value" id="active-borrowers-count">
                                {{ number_format($analytics['active_borrowers']['count'] ?? 0) }}
                            </div>
                            <div class="stat-change">
                                <i class="fas fa-percentage"></i>
                                {{ number_format($analytics['active_borrowers']['percentage'] ?? 0, 1) }}% Penetration Rate
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Repayment Rate -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Repayment Rate</div>
                            <div class="stat-value" id="repayment-rate">
                                {{ number_format(100 - ($analytics['portfolio_at_risk']['total_par']['percentage'] ?? 0), 1) }}%
                            </div>
                            <div class="stat-change">
                                <i class="fas fa-money-bill-wave"></i>
                                ${{ number_format(($analytics['repayments_collected']['total'] ?? 0) / 1000, 0) }}K Collected
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Performance Row -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Released Principal
                    </div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800" id="released-principal">
                        ${{ number_format($analytics['released_principal']['total'] ?? 0, 0) }}
                    </div>
                    <div class="text-muted small mt-2">
                        This Month: ${{ number_format($analytics['released_principal']['this_month'] ?? 0, 0) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Interest Collected
                    </div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800" id="interest-collected">
                        ${{ number_format($analytics['interest_collected']['total'] ?? 0, 0) }}
                    </div>
                    <div class="text-muted small mt-2">
                        This Month: ${{ number_format($analytics['interest_collected']['this_month'] ?? 0, 0) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-info h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Repayments Collected
                    </div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800" id="repayments-collected">
                        ${{ number_format($analytics['repayments_collected']['total'] ?? 0, 0) }}
                    </div>
                    <div class="text-muted small mt-2">
                        This Month: ${{ number_format($analytics['repayments_collected']['this_month'] ?? 0, 0) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        Pending Approvals
                    </div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800" id="pending-loans">
                        {{ number_format($analytics['pending_loans']['count'] ?? 0) }}
                    </div>
                    <div class="text-muted small mt-2">
                        Amount: ${{ number_format($analytics['pending_loans']['amount'] ?? 0, 0) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Portfolio at Risk -->
    <div class="row g-3 mb-4">
        <div class="col-xl-12">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i>Portfolio at Risk (PAR)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="par-metric">
                                <div class="par-value text-warning" id="par-14-value">
                                    {{ number_format($analytics['portfolio_at_risk']['14_day_par']['percentage'] ?? 0, 2) }}%
                                </div>
                                <div class="par-label">14-Day PAR</div>
                                <div class="par-amount text-muted">
                                    ${{ number_format($analytics['portfolio_at_risk']['14_day_par']['amount'] ?? 0, 0) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="par-metric">
                                <div class="par-value text-danger" id="par-30-value">
                                    {{ number_format($analytics['portfolio_at_risk']['30_day_par']['percentage'] ?? 0, 2) }}%
                                </div>
                                <div class="par-label">30-Day PAR</div>
                                <div class="par-amount text-muted">
                                    ${{ number_format($analytics['portfolio_at_risk']['30_day_par']['amount'] ?? 0, 0) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="par-metric">
                                <div class="par-value text-danger" id="par-over30-value">
                                    {{ number_format($analytics['portfolio_at_risk']['over_30_day_par']['percentage'] ?? 0, 2) }}%
                                </div>
                                <div class="par-label">Over 30-Day PAR</div>
                                <div class="par-amount text-muted">
                                    ${{ number_format($analytics['portfolio_at_risk']['over_30_day_par']['amount'] ?? 0, 0) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="par-metric">
                                <div class="par-value text-danger" id="par-total-value">
                                    {{ number_format($analytics['portfolio_at_risk']['total_par']['percentage'] ?? 0, 2) }}%
                                </div>
                                <div class="par-label">Total PAR</div>
                                <div class="par-amount text-muted">
                                    ${{ number_format($analytics['portfolio_at_risk']['total_par']['amount'] ?? 0, 0) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-Time Loan Applications & Approvals -->
    <div class="row g-3 mb-4">
        <!-- Pending Approvals -->
        <div class="col-xl-4">
            <div class="card shadow h-100">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-clock me-2"></i>Pending Approvals
                        <span class="badge bg-light text-dark ms-2" id="pending-approvals-count">{{ $analytics['pending_loans']['count'] ?? 0 }}</span>
                        <div class="float-end">
                            <button class="btn btn-sm btn-outline-light" onclick="refreshApprovals()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div id="pending-approvals-list" class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                        @if(isset($data['recent_activities']) && count($data['recent_activities']) > 0)
                            @foreach($data['recent_activities']->where('status', 'pending')->take(5) as $application)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $application->client->first_name ?? 'N/A' }} {{ $application->client->last_name ?? '' }}</h6>
                                    <p class="mb-1 text-muted small">Amount: ${{ number_format($application->amount ?? 0, 2) }}</p>
                                    <small class="text-muted">{{ $application->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-success" onclick="approveLoan({{ $application->id }})">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="rejectLoan({{ $application->id }})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <a href="{{ route('loans.show', $application->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <p class="text-muted">No pending approvals!</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Application Feed -->
        <div class="col-xl-4">
            <div class="card shadow h-100">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-rss me-2"></i>Live Application Feed
                        <div class="float-end">
                            <span class="badge bg-light text-dark" id="live-indicator">LIVE</span>
                        </div>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div id="live-feed" class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                        @if(isset($data['recent_activities']) && count($data['recent_activities']) > 0)
                            @foreach($data['recent_activities']->take(10) as $activity)
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="status-icon me-3">
                                        <i class="fas fa-{{ $activity->status === 'pending' ? 'clock text-warning' : ($activity->status === 'approved' ? 'check-circle text-success' : 'times-circle text-danger') }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $activity->client->first_name ?? 'N/A' }} applied for loan</h6>
                                        <p class="mb-1 text-muted small">
                                            ${{ number_format($activity->amount ?? 0, 2) }} • 
                                            <span class="badge bg-{{ $activity->status === 'pending' ? 'warning' : ($activity->status === 'approved' ? 'success' : 'danger') }}">
                                                {{ ucfirst($activity->status ?? 'pending') }}
                                            </span>
                                        </p>
                                        <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No recent activity</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-xl-4">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('loans.create') }}" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i>New Loan Application
                        </a>
                        <a href="{{ route('clients.create') }}" class="btn btn-outline-success">
                            <i class="fas fa-user-plus me-2"></i>Add New Client
                        </a>
                        <button class="btn btn-outline-info" onclick="generateReports()">
                            <i class="fas fa-chart-line me-2"></i>Generate Reports
                        </button>
                        <button class="btn btn-outline-warning" onclick="exportData()">
                            <i class="fas fa-download me-2"></i>Export Data
                        </button>
                    </div>
                    
                    <hr>
                    
                    <!-- System Health -->
                    <div class="text-center">
                        <h6 class="text-muted">System Health</h6>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="health-metric">
                                    <div class="text-success h4">98%</div>
                                    <small class="text-muted">Uptime</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="health-metric">
                                    <div class="text-info h4">{{ $analytics['active_borrowers']['count'] ?? 0 }}</div>
                                    <small class="text-muted">Online</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="health-metric">
                                    <div class="text-warning h4">0</div>
                                    <small class="text-muted">Alerts</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Tables Row -->
    <div class="row g-3 mb-4">
        <!-- Loans Due Today -->
        <div class="col-xl-6">
            <div class="card shadow h-100">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-clock me-2"></i>Loans Due Today
                        <span class="badge bg-light text-dark ms-2">{{ $analytics['loans_due_today']['count'] ?? 0 }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($analytics['loans_due_today']['loans']) && count($analytics['loans_due_today']['loans']) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Loan #</th>
                                        <th>Client</th>
                                        <th>Amount Due</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analytics['loans_due_today']['loans'] as $loan)
                                    <tr>
                                        <td><strong>{{ $loan->loan_number ?? 'N/A' }}</strong></td>
                                        <td>{{ $loan->client->first_name ?? 'N/A' }} {{ $loan->client->last_name ?? '' }}</td>
                                        <td>${{ number_format($loan->next_payment_amount ?? 0, 2) }}</td>
                                        <td>
                                            <a href="{{ route('loans.show', $loan->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">No loans due today!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Loan Applications -->
        <div class="col-xl-6">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-file-alt me-2"></i>Recent Loan Applications
                        <span class="badge bg-light text-dark ms-2">{{ $analytics['loan_requests']['count'] ?? 0 }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($data['recent_activities']) && count($data['recent_activities']) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['recent_activities']->take(5) as $activity)
                                    <tr>
                                        <td>{{ $activity->created_at->format('M d, Y') }}</td>
                                        <td>{{ $activity->client->first_name ?? 'N/A' }}</td>
                                        <td>${{ number_format($activity->amount ?? 0, 0) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $activity->status === 'pending' ? 'warning' : ($activity->status === 'approved' ? 'success' : 'info') }}">
                                                {{ ucfirst($activity->status ?? 'pending') }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No recent applications</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- System Overview -->
    <div class="row g-3 mb-4">
        <div class="col-xl-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i>System Overview & Analytics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <canvas id="loanStatusChart"></canvas>
                        </div>
                        <div class="col-md-4">
                            <canvas id="portfolioChart"></canvas>
                        </div>
                        <div class="col-md-4">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 12px rgba(0,0,0,0.15);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.stat-label {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    opacity: 0.9;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-change {
    font-size: 0.875rem;
    opacity: 0.9;
}

.stat-icon {
    opacity: 0.3;
}

.border-left-primary {
    border-left: 4px solid #4e73df !important;
}

.border-left-success {
    border-left: 4px solid #1cc88a !important;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.par-metric {
    padding: 1rem;
    border-radius: 10px;
    background: #f8f9fa;
    margin: 0.5rem;
}

.par-value {
    font-size: 2rem;
    font-weight: 700;
}

.par-label {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #6c757d;
    margin-top: 0.5rem;
}

.par-amount {
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>
@endsection

@section('scripts')
<script>
// Real-time Dashboard Refresh
let refreshInterval;

function refreshDashboard() {
    const refreshBtn = document.querySelector('button[onclick="refreshDashboard()"]');
    const icon = refreshBtn?.querySelector('i');
    
    if (icon) {
        icon.classList.add('fa-spin');
        refreshBtn.disabled = true;
    }
    
    fetch('{{ route("admin.dashboard.realtime") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardMetrics(data.data);
                updateLastUpdateTime();
                showNotification('Dashboard refreshed successfully!', 'success');
            } else {
                showNotification('Error refreshing dashboard', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error refreshing dashboard', 'error');
        })
        .finally(() => {
            if (icon) {
                icon.classList.remove('fa-spin');
                refreshBtn.disabled = false;
            }
        });
}

function updateDashboardMetrics(data) {
    // Update key metrics
    if (data.active_loans) {
        document.getElementById('total-loans').textContent = formatNumber(data.active_loans.count);
    }
    if (data.active_borrowers) {
        document.getElementById('active-borrowers-count').textContent = formatNumber(data.active_borrowers.count);
    }
    if (data.outstanding_principal) {
        document.getElementById('outstanding-principal-total').textContent = '$' + formatNumber(Math.round(data.outstanding_principal.total / 1000)) + 'K';
    }
    if (data.overdue_loans) {
        document.getElementById('overdue-count').textContent = formatNumber(data.overdue_loans.count);
    }
    
    // Update PAR metrics
    if (data.portfolio_at_risk) {
        document.getElementById('par-14-value').textContent = data.portfolio_at_risk['14_day_par'].percentage.toFixed(2) + '%';
        document.getElementById('par-30-value').textContent = data.portfolio_at_risk['30_day_par'].percentage.toFixed(2) + '%';
        document.getElementById('par-over30-value').textContent = data.portfolio_at_risk.over_30_day_par.percentage.toFixed(2) + '%';
        document.getElementById('par-total-value').textContent = data.portfolio_at_risk.total_par.percentage.toFixed(2) + '%';
    }
}

function updateLastUpdateTime() {
    const element = document.getElementById('last-update-time');
    if (element) {
        const now = new Date();
        element.textContent = now.toLocaleTimeString();
    }
}

function formatNumber(num) {
    return new Intl.NumberFormat().format(num);
}

function showNotification(message, type) {
    // Simple notification - you can replace with toast library
    console.log(`[${type}] ${message}`);
}

function exportData() {
    window.location.href = '{{ route("admin.dashboard.export") }}?format=csv';
}

// Real-time loan approval functions
function approveLoan(loanId) {
    if (confirm('Are you sure you want to approve this loan?')) {
        fetch(`/admin/loans/${loanId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Loan approved successfully!', 'success');
                refreshApprovals();
                refreshDashboard();
            } else {
                showNotification(data.message || 'Error approving loan', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Network error occurred', 'error');
        });
    }
}

function rejectLoan(loanId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason && reason.trim()) {
        fetch(`/admin/loans/${loanId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ reason: reason.trim() })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Loan rejected successfully!', 'warning');
                refreshApprovals();
                refreshDashboard();
            } else {
                showNotification(data.message || 'Error rejecting loan', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Network error occurred', 'error');
        });
    }
}

function refreshApprovals() {
    fetch('/admin/dashboard/pending-approvals')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updatePendingApprovalsList(data.approvals);
                document.getElementById('pending-approvals-count').textContent = data.count;
            }
        })
        .catch(error => console.error('Error refreshing approvals:', error));
}

function updatePendingApprovalsList(approvals) {
    const container = document.getElementById('pending-approvals-list');
    if (approvals.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p class="text-muted">No pending approvals!</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = approvals.map(application => `
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">${application.client_name}</h6>
                <p class="mb-1 text-muted small">Amount: $${new Intl.NumberFormat().format(application.amount)}</p>
                <small class="text-muted">${application.created_at}</small>
            </div>
            <div class="btn-group">
                <button class="btn btn-sm btn-success" onclick="approveLoan(${application.id})">
                    <i class="fas fa-check"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="rejectLoan(${application.id})">
                    <i class="fas fa-times"></i>
                </button>
                <a href="/loans/${application.id}" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i>
                </a>
            </div>
        </div>
    `).join('');
}

function generateReports() {
    // Show report generation modal or redirect to reports page
    window.location.href = '/admin/reports';
}

// Live feed updates
function updateLiveFeed() {
    fetch('/admin/dashboard/live-feed')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateLiveFeedList(data.activities);
                // Update live indicator
                const indicator = document.getElementById('live-indicator');
                indicator.classList.remove('bg-light');
                indicator.classList.add('bg-success');
                setTimeout(() => {
                    indicator.classList.remove('bg-success');
                    indicator.classList.add('bg-light');
                }, 1000);
            }
        })
        .catch(error => console.error('Error updating live feed:', error));
}

function updateLiveFeedList(activities) {
    const container = document.getElementById('live-feed');
    if (activities.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No recent activity</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = activities.map(activity => `
        <div class="list-group-item">
            <div class="d-flex align-items-center">
                <div class="status-icon me-3">
                    <i class="fas fa-${activity.status === 'pending' ? 'clock text-warning' : (activity.status === 'approved' ? 'check-circle text-success' : 'times-circle text-danger')}"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">${activity.client_name} applied for loan</h6>
                    <p class="mb-1 text-muted small">
                        $${new Intl.NumberFormat().format(activity.amount)} • 
                        <span class="badge bg-${activity.status === 'pending' ? 'warning' : (activity.status === 'approved' ? 'success' : 'danger')}">
                            ${activity.status.charAt(0).toUpperCase() + activity.status.slice(1)}
                        </span>
                    </p>
                    <small class="text-muted">${activity.created_at}</small>
                </div>
            </div>
        </div>
    `).join('');
}

// Auto-refresh every 30 seconds
document.addEventListener('DOMContentLoaded', function() {
    refreshInterval = setInterval(refreshDashboard, 30000);
    
    // Initialize live feed refresh every 10 seconds
    setInterval(updateLiveFeed, 10000);
    
    // Initialize approvals refresh every 15 seconds
    setInterval(refreshApprovals, 15000);
    
    // Initialize charts
    initializeCharts();
});

function initializeCharts() {
    // Loan Status Chart
    const loanStatusCtx = document.getElementById('loanStatusChart');
    if (loanStatusCtx) {
        new Chart(loanStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Pending', 'Overdue', 'Completed'],
                datasets: [{
                    data: [
                        {{ $analytics['active_loans']['count'] ?? 0 }},
                        {{ $analytics['pending_loans']['count'] ?? 0 }},
                        {{ $analytics['overdue_loans']['count'] ?? 0 }},
                        {{ $analytics['loan_release_vs_completed']['completed'] ?? 0 }}
                    ],
                    backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b', '#4e73df']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Loan Status Distribution'
                    }
                }
            }
        });
    }
    
    // Portfolio Chart
    const portfolioCtx = document.getElementById('portfolioChart');
    if (portfolioCtx) {
        new Chart(portfolioCtx, {
            type: 'pie',
            data: {
                labels: ['Outstanding', 'Collected'],
                datasets: [{
                    data: [
                        {{ $analytics['outstanding_principal']['total'] ?? 0 }},
                        {{ $analytics['repayments_collected']['total'] ?? 0 }}
                    ],
                    backgroundColor: ['#f6c23e', '#1cc88a']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Portfolio Overview'
                    }
                }
            }
        });
    }
    
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: ['Interest', 'Fees', 'Penalties'],
                datasets: [{
                    label: 'Revenue',
                    data: [
                        {{ $analytics['interest_collected']['total'] ?? 0 }},
                        {{ $analytics['charged_fees']['total'] ?? 0 }},
                        {{ $analytics['penalties_collected']['total'] ?? 0 }}
                    ],
                    backgroundColor: '#4e73df'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Revenue Breakdown'
                    }
                }
            }
        });
    }
}
</script>
@endsection
