@extends('layouts.app')

@section('title', 'Branch Manager Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-building text-primary me-2"></i>Branch Manager Dashboard
            </h1>
            <p class="text-muted mb-0">{{ auth()->user()->branch->name ?? 'Branch' }} Performance & Operations</p>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-info" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button type="button" class="btn btn-primary" onclick="exportReport()">
                <i class="fas fa-download"></i> Export Report
            </button>
        </div>
    </div>

    <!-- Real-time Status Bar -->
    <div class="alert alert-info d-flex align-items-center justify-content-between mb-4">
        <div>
            <i class="fas fa-circle text-success me-2 pulse-animation"></i>
            <strong>Branch Live Data:</strong> Last updated <span id="last-update-time">just now</span>
        </div>
        <small class="text-muted">Auto-refreshes every 30 seconds</small>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Branch Performance Metrics -->
    <div class="row g-3 mb-4">
        <!-- Branch Loans -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Branch Loans</div>
                            <div class="stat-value" id="branch-total-loans">
                                {{ number_format($analytics['active_loans']['count'] ?? 0) }}
                            </div>
                            <div class="stat-change">
                                <i class="fas fa-arrow-up"></i>
                                ${{ number_format(($analytics['active_loans']['outstanding'] ?? 0) / 1000, 1) }}K Active
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-hand-holding-usd fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branch Clients -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Active Clients</div>
                            <div class="stat-value" id="branch-clients">
                                {{ number_format($analytics['active_borrowers']['count'] ?? 0) }}
                            </div>
                            <div class="stat-change">
                                <i class="fas fa-percentage"></i>
                                {{ number_format($analytics['active_borrowers']['percentage'] ?? 0, 1) }}% Active
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branch Portfolio -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Branch Portfolio</div>
                            <div class="stat-value" id="branch-portfolio">
                                ${{ number_format(($analytics['outstanding_principal']['total'] ?? 0) / 1000, 0) }}K
                            </div>
                            <div class="stat-change">
                                <i class="fas fa-chart-line"></i>
                                Outstanding Balance
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-wallet fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branch Collection Rate -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Collection Rate</div>
                            <div class="stat-value" id="branch-collection-rate">
                                {{ number_format(100 - ($analytics['portfolio_at_risk']['total_par']['percentage'] ?? 0), 1) }}%
                            </div>
                            <div class="stat-change">
                                <i class="fas fa-check-circle"></i>
                                On-time Payments
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-percentage fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Performance -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        Loans Due Today
                    </div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800" id="loans-due-today">
                        {{ $analytics['loans_due_today']['count'] ?? 0 }}
                    </div>
                    <div class="text-muted small mt-2">
                        Amount: ${{ number_format($analytics['loans_due_today']['amount'] ?? 0, 0) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-danger h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                        Overdue Loans
                    </div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800" id="overdue-loans">
                        {{ $analytics['overdue_loans']['count'] ?? 0 }}
                    </div>
                    <div class="text-muted small mt-2">
                        Amount: ${{ number_format($analytics['overdue_loans']['amount'] ?? 0, 0) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Pending Applications
                    </div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800" id="pending-apps">
                        {{ $analytics['loan_requests']['count'] ?? 0 }}
                    </div>
                    <div class="text-muted small mt-2">
                        Amount: ${{ number_format($analytics['loan_requests']['amount'] ?? 0, 0) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Collections Today
                    </div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800" id="collections-today">
                        ${{ number_format($analytics['repayments_collected']['this_month'] ?? 0, 0) }}
                    </div>
                    <div class="text-muted small mt-2">
                        This Month Collections
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch PAR Analysis -->
    <div class="row g-3 mb-4">
        <div class="col-xl-12">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i>Branch Portfolio at Risk (PAR)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="par-metric">
                                <div class="par-value text-warning" id="branch-par-14">
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
                                <div class="par-value text-danger" id="branch-par-30">
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
                                <div class="par-value text-danger" id="branch-par-over30">
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
                                <div class="par-value text-danger" id="branch-par-total">
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

    <!-- Branch Activities -->
    <div class="row g-3 mb-4">
        <!-- Today's Collections -->
        <div class="col-xl-6">
            <div class="card shadow h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-money-bill-wave me-2"></i>Today's Collections
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
                                        <td>{{ $loan->client->first_name ?? 'N/A' }}</td>
                                        <td>${{ number_format($loan->next_payment_amount ?? 0, 2) }}</td>
                                        <td>
                                            <a href="{{ route('loans.show', $loan->id) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-dollar-sign"></i>
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
                            <p class="text-muted">No collections due today!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Overdue Loans -->
        <div class="col-xl-6">
            <div class="card shadow h-100">
                <div class="card-header bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-circle me-2"></i>Overdue Loans - Action Required
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($analytics['overdue_loans']['loans']) && count($analytics['overdue_loans']['loans']) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Loan #</th>
                                        <th>Client</th>
                                        <th>Overdue</th>
                                        <th>Days</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analytics['overdue_loans']['loans'] as $loan)
                                    <tr>
                                        <td><strong>{{ $loan->loan_number ?? 'N/A' }}</strong></td>
                                        <td>{{ $loan->client->first_name ?? 'N/A' }}</td>
                                        <td>${{ number_format($loan->outstanding_balance ?? 0, 2) }}</td>
                                        <td>
                                            <span class="badge bg-danger">
                                                {{ $loan->next_due_date ? $loan->next_due_date->diffInDays(now()) : 0 }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-thumbs-up fa-3x text-success mb-3"></i>
                            <p class="text-muted">No overdue loans - Great job!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Charts -->
    <div class="row g-3 mb-4">
        <div class="col-xl-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-area me-2"></i>Branch Performance Charts
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="branchPortfolioChart"></canvas>
                        </div>
                        <div class="col-md-6">
                            <canvas id="branchPerformanceChart"></canvas>
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
    
    fetch('{{ route("branch-manager.dashboard.realtime") }}')
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
        document.getElementById('branch-total-loans').textContent = formatNumber(data.active_loans.count);
    }
    if (data.active_borrowers) {
        document.getElementById('branch-clients').textContent = formatNumber(data.active_borrowers.count);
    }
    if (data.outstanding_principal) {
        document.getElementById('branch-portfolio').textContent = '$' + formatNumber(Math.round(data.outstanding_principal.total / 1000)) + 'K';
    }
    if (data.loans_due_today) {
        document.getElementById('loans-due-today').textContent = formatNumber(data.loans_due_today.count);
    }
    if (data.overdue_loans) {
        document.getElementById('overdue-loans').textContent = formatNumber(data.overdue_loans.count);
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

function exportReport() {
    window.location.href = '{{ route("branch-manager.dashboard.export") }}?format=csv';
}

document.addEventListener('DOMContentLoaded', function() {
    refreshInterval = setInterval(refreshDashboard, 30000);
    initializeCharts();
});

function initializeCharts() {
    // Branch Portfolio Chart
    const portfolioCtx = document.getElementById('branchPortfolioChart');
    if (portfolioCtx) {
        new Chart(portfolioCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Overdue', 'Pending'],
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
                        text: 'Branch Loan Portfolio'
                    }
                }
            }
        });
    }
    
    // Branch Performance Chart
    const performanceCtx = document.getElementById('branchPerformanceChart');
    if (performanceCtx) {
        new Chart(performanceCtx, {
            type: 'bar',
            data: {
                labels: ['Disbursed', 'Collections', 'Interest', 'Outstanding'],
                datasets: [{
                    label: 'Amount ($)',
                    data: [
                        {{ $analytics['released_principal']['total'] ?? 0 }},
                        {{ $analytics['repayments_collected']['total'] ?? 0 }},
                        {{ $analytics['interest_collected']['total'] ?? 0 }},
                        {{ $analytics['outstanding_principal']['total'] ?? 0 }}
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
                        text: 'Branch Financial Performance'
                    }
                }
            }
        });
    }
}
</script>
@endsection
