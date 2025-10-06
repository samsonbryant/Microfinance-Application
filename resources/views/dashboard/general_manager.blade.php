@extends('layouts.app')

@section('title', 'General Manager Dashboard')

@section('content')
<!-- General Manager Dashboard - Management Overview -->
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info border-0">
                <i class="fas fa-user-tie me-2"></i>
                <strong>General Manager Dashboard</strong> - Comprehensive management overview and operational insights.
            </div>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value">{{ $stats['total_clients'] ?? 0 }}</div>
                    <div class="stat-label">Total Clients</div>
                    <div class="mt-2">
                        <small class="opacity-75">Active: {{ $stats['active_clients'] ?? 0 }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white">
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
            <div class="card stat-card bg-info text-white">
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
            <div class="card stat-card bg-warning text-white">
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
                                    <th>Performance</th>
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
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-{{ ($branch->performance_score ?? 0) > 80 ? 'success' : (($branch->performance_score ?? 0) > 60 ? 'warning' : 'danger') }}" 
                                                     style="width: {{ $branch->performance_score ?? 0 }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $branch->performance_score ?? 0 }}%</small>
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

    <!-- Loan Performance Metrics -->
    <div class="row mb-4">
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

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>Monthly Trends
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyTrendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Recent Activities
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Activity</th>
                                    <th>User</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities['recent_transactions'] ?? [] as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('M d, H:i') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->type === 'loan_disbursement' ? 'success' : 'info' }}">
                                                {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                            </span>
                                        </td>
                                        <td>{{ $transaction->client->full_name ?? 'N/A' }}</td>
                                        <td>${{ number_format($transaction->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No recent activities</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
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
                        <a href="{{ route('reports.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-chart-bar me-2"></i>Generate Reports
                        </a>
                        <a href="{{ route('collections.index') }}" class="btn btn-outline-warning">
                            <i class="fas fa-credit-card me-2"></i>Collections
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Portfolio Health Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-shield-alt me-2"></i>Portfolio Health Summary
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

// Monthly Trends Chart
const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
const monthlyTrendsChart = new Chart(monthlyTrendsCtx, {
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
        }, {
            label: 'Collections',
            data: @json($monthlyTrends->pluck('loans_collected')),
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
        }
    }
});
</script>
@endsection
