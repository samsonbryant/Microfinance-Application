@extends('layouts.app')

@section('title', 'Loan Officer Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-tie text-info me-2"></i>My Portfolio Dashboard
            </h1>
            <p class="text-muted mb-0">{{ auth()->user()->name }} - Personal Loan Portfolio</p>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-info" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <a href="{{ route('loan-applications.index') }}" class="btn btn-primary">
                <i class="fas fa-file-alt"></i> View Applications
            </a>
        </div>
    </div>

    <!-- Real-time Status Bar -->
    <div class="alert alert-info d-flex align-items-center justify-content-between mb-4">
        <div>
            <i class="fas fa-circle text-success me-2 pulse-animation"></i>
            <strong>My Portfolio Live:</strong> Last updated <span id="last-update-time">just now</span>
        </div>
        <small class="text-muted">Auto-refreshes every 30 seconds</small>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Personal Performance Metrics -->
    <div class="row g-3 mb-4">
        <!-- My Loans -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">My Active Loans</div>
                            <div class="stat-value" id="my-total-loans">
                                {{ number_format($analytics['active_loans']['count'] ?? 0) }}
                            </div>
                            <div class="stat-change">
                                <i class="fas fa-dollar-sign"></i>
                                ${{ number_format(($analytics['active_loans']['outstanding'] ?? 0) / 1000, 1) }}K Outstanding
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-briefcase fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Clients -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">My Active Clients</div>
                            <div class="stat-value" id="my-clients">
                                {{ number_format($analytics['active_borrowers']['count'] ?? 0) }}
                            </div>
                            <div class="stat-change">
                                <i class="fas fa-user-check"></i>
                                {{ number_format($analytics['active_borrowers']['percentage'] ?? 0, 1) }}% Active Rate
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Portfolio Value -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">My Portfolio Value</div>
                            <div class="stat-value" id="my-portfolio">
                                ${{ number_format(($analytics['outstanding_principal']['total'] ?? 0) / 1000, 0) }}K
                            </div>
                            <div class="stat-change">
                                <i class="fas fa-chart-line"></i>
                                Total Outstanding
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-chart-pie fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Collections -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">My Collections</div>
                            <div class="stat-value" id="my-collections">
                                ${{ number_format(($analytics['repayments_collected']['this_month'] ?? 0) / 1000, 1) }}K
                            </div>
                            <div class="stat-change">
                                <i class="fas fa-calendar-alt"></i>
                                This Month
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-hand-holding-usd fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Tasks -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        Due Today
                    </div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800" id="my-due-today">
                        {{ $analytics['loans_due_today']['count'] ?? 0 }}
                    </div>
                    <div class="text-muted small mt-2">
                        ${{ number_format($analytics['loans_due_today']['amount'] ?? 0, 0) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-danger h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                        Overdue
                    </div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800" id="my-overdue">
                        {{ $analytics['overdue_loans']['count'] ?? 0 }}
                    </div>
                    <div class="text-muted small mt-2">
                        ${{ number_format($analytics['overdue_loans']['amount'] ?? 0, 0) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        New Applications
                    </div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800" id="my-pending-apps">
                        {{ $analytics['loan_requests']['count'] ?? 0 }}
                    </div>
                    <div class="text-muted small mt-2">
                        Needs Review
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Portfolio Quality
                    </div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800" id="my-quality-score">
                        {{ number_format(100 - ($analytics['portfolio_at_risk']['total_par']['percentage'] ?? 0), 1) }}%
                    </div>
                    <div class="text-muted small mt-2">
                        On-time Rate
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- My Portfolio Risk -->
    <div class="row g-3 mb-4">
        <div class="col-xl-12">
            <div class="card shadow">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-shield-alt me-2"></i>My Portfolio Risk Analysis
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="par-metric">
                                <div class="par-value text-warning" id="my-par-30">
                                    {{ number_format($analytics['portfolio_at_risk']['30_day_par']['percentage'] ?? 0, 2) }}%
                                </div>
                                <div class="par-label">30-Day PAR</div>
                                <div class="par-amount text-muted">
                                    ${{ number_format($analytics['portfolio_at_risk']['30_day_par']['amount'] ?? 0, 0) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="par-metric">
                                <div class="par-value text-danger" id="my-par-over30">
                                    {{ number_format($analytics['portfolio_at_risk']['over_30_day_par']['percentage'] ?? 0, 2) }}%
                                </div>
                                <div class="par-label">Over 30-Day PAR</div>
                                <div class="par-amount text-muted">
                                    ${{ number_format($analytics['portfolio_at_risk']['over_30_day_par']['amount'] ?? 0, 0) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="par-metric">
                                <div class="par-value text-info" id="my-default-rate">
                                    {{ number_format($analytics['default_rate']['percentage'] ?? 0, 2) }}%
                                </div>
                                <div class="par-label">Default Rate</div>
                                <div class="par-amount text-muted">
                                    {{ $analytics['default_rate']['count'] ?? 0 }} Loans
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- My Daily Activities -->
    <div class="row g-3 mb-4">
        <!-- My Loans Due Today -->
        <div class="col-xl-6">
            <div class="card shadow h-100">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-clock me-2"></i>My Collections Due Today
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
                                        <th>Expected</th>
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
                                            <a href="{{ route('loans.show', $loan->id) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-phone"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-coffee fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No collections due today - You're all set!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- My Pending Applications -->
        <div class="col-xl-6">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-file-alt me-2"></i>Applications Awaiting My Review
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
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['recent_activities']->take(5) as $activity)
                                    <tr>
                                        <td>{{ $activity->created_at->format('M d') }}</td>
                                        <td>{{ $activity->client->first_name ?? 'N/A' }}</td>
                                        <td>${{ number_format($activity->amount ?? 0, 0) }}</td>
                                        <td>
                                            <a href="{{ route('loan-applications.show', $activity->id) }}" class="btn btn-sm btn-primary">
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
                            <p class="text-muted">No pending applications!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- My Performance Charts -->
    <div class="row g-3 mb-4">
        <div class="col-xl-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>My Performance Analytics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="myPortfolioChart"></canvas>
                        </div>
                        <div class="col-md-6">
                            <canvas id="myCollectionsChart"></canvas>
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

.bg-gradient-info {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
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

.border-left-danger {
    border-left: 4px solid #e74a3b !important;
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
let refreshInterval;

function refreshDashboard() {
    const refreshBtn = document.querySelector('button[onclick="refreshDashboard()"]');
    const icon = refreshBtn?.querySelector('i');
    
    if (icon) {
        icon.classList.add('fa-spin');
        refreshBtn.disabled = true;
    }
    
    fetch('{{ route("loan-officer.dashboard.realtime") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardMetrics(data.data);
                updateLastUpdateTime();
            }
        })
        .catch(error => console.error('Error:', error))
        .finally(() => {
            if (icon) {
                icon.classList.remove('fa-spin');
                refreshBtn.disabled = false;
            }
        });
}

function updateDashboardMetrics(data) {
    if (data.active_loans) {
        document.getElementById('my-total-loans').textContent = formatNumber(data.active_loans.count);
    }
    if (data.active_borrowers) {
        document.getElementById('my-clients').textContent = formatNumber(data.active_borrowers.count);
    }
    if (data.outstanding_principal) {
        document.getElementById('my-portfolio').textContent = '$' + formatNumber(Math.round(data.outstanding_principal.total / 1000)) + 'K';
    }
    if (data.repayments_collected) {
        document.getElementById('my-collections').textContent = '$' + formatNumber(Math.round(data.repayments_collected.this_month / 1000)) + 'K';
    }
}

function updateLastUpdateTime() {
    const element = document.getElementById('last-update-time');
    if (element) {
        element.textContent = new Date().toLocaleTimeString();
    }
}

function formatNumber(num) {
    return new Intl.NumberFormat().format(num);
}

document.addEventListener('DOMContentLoaded', function() {
    refreshInterval = setInterval(refreshDashboard, 30000);
    initializeCharts();
});

function initializeCharts() {
    // My Portfolio Chart
    const portfolioCtx = document.getElementById('myPortfolioChart');
    if (portfolioCtx) {
        new Chart(portfolioCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active & Performing', 'Overdue', 'Pending Review'],
                datasets: [{
                    data: [
                        {{ $analytics['active_loans']['count'] ?? 0 }},
                        {{ $analytics['overdue_loans']['count'] ?? 0 }},
                        {{ $analytics['loan_requests']['count'] ?? 0 }}
                    ],
                    backgroundColor: ['#1cc88a', '#e74a3b', '#f6c23e']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'My Loan Portfolio Status'
                    }
                }
            }
        });
    }
    
    // My Collections Chart
    const collectionsCtx = document.getElementById('myCollectionsChart');
    if (collectionsCtx) {
        new Chart(collectionsCtx, {
            type: 'bar',
            data: {
                labels: ['Principal', 'Interest', 'Fees', 'Total Collected'],
                datasets: [{
                    label: 'Amount ($)',
                    data: [
                        {{ $analytics['released_principal']['this_month'] ?? 0 }},
                        {{ $analytics['interest_collected']['this_month'] ?? 0 }},
                        {{ $analytics['charged_fees']['this_month'] ?? 0 }},
                        {{ $analytics['repayments_collected']['this_month'] ?? 0 }}
                    ],
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'My Collections This Month'
                    }
                }
            }
        });
    }
}
</script>
@endsection
