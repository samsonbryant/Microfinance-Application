@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

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
                        <small class="opacity-75">Active: ${{ number_format($stats['active_loans'] ?? 0, 2) }}</small>
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

    <!-- Financial Health Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-danger text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value">{{ $stats['overdue_loans'] ?? 0 }}</div>
                    <div class="stat-label">Overdue Loans</div>
                    <div class="mt-2">
                        <small class="opacity-75">Amount: ${{ number_format($stats['overdue_amount'] ?? 0, 2) }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-secondary text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-value">{{ number_format($stats['par_percentage'] ?? 0, 2) }}%</div>
                    <div class="stat-label">Portfolio at Risk</div>
                    <div class="mt-2">
                        <small class="opacity-75">Target: < 5%</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-dark text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['monthly_revenue'] ?? 0, 2) }}</div>
                    <div class="stat-label">Monthly Revenue</div>
                    <div class="mt-2">
                        <small class="opacity-75">Growth: {{ $stats['revenue_growth'] ?? 0 }}%</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['net_profit'] ?? 0, 2) }}</div>
                    <div class="stat-label">Net Profit</div>
                    <div class="mt-2">
                        <small class="opacity-75">Margin: {{ $stats['profit_margin'] ?? 0 }}%</small>
                    </div>
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
                            <a href="{{ route('backup.create') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-database me-2"></i>Backup System
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('settings.index') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-cog me-2"></i>System Settings
                            </a>
                        </div>
                    </div>
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
                                            <span class="badge bg-{{ $log->getEventBadgeClass() }}">
                                                {{ $log->getEventText() }}
                                            </span>
                                        </td>
                                        <td>{{ $log->description }}</td>
                                        <td>{{ $log->ip_address ?? 'N/A' }}</td>
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
</style>
@endsection

@section('scripts')
<script>
// Loan Disbursements Chart
const loanDisbursementsCtx = document.getElementById('loanDisbursementsChart').getContext('2d');
const loanDisbursementsChart = new Chart(loanDisbursementsCtx, {
    type: 'line',
    data: {
        labels: @json($monthlyTrends->pluck('month')),
        datasets: [{
            label: 'Loan Disbursements',
            data: @json($monthlyTrends->pluck('loans_disbursed')),
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
</script>
@endsection