@extends('layouts.app')

@section('title', 'Loan Officer Dashboard')

@section('content')
<!-- Loan Officer Dashboard - Loan Management Focus -->
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info border-0">
                <i class="fas fa-user-tie me-2"></i>
                <strong>Loan Officer Dashboard</strong> - Manage loans, clients, and collections efficiently.
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-value">{{ $stats['pending_applications'] ?? 0 }}</div>
                    <div class="stat-label">Pending Applications</div>
                    <div class="mt-2">
                        <small class="opacity-75">Requires Review</small>
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
                    <div class="stat-value">{{ $stats['active_loans'] ?? 0 }}</div>
                    <div class="stat-label">Active Loans</div>
                    <div class="mt-2">
                        <small class="opacity-75">Value: ${{ number_format($stats['active_loan_value'] ?? 0, 2) }}</small>
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
                    <div class="stat-value">{{ $stats['overdue_loans'] ?? 0 }}</div>
                    <div class="stat-label">Overdue Loans</div>
                    <div class="mt-2">
                        <small class="opacity-75">Amount: ${{ number_format($stats['overdue_amount'] ?? 0, 2) }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value">{{ $stats['my_clients'] ?? 0 }}</div>
                    <div class="stat-label">My Clients</div>
                    <div class="mt-2">
                        <small class="opacity-75">Active Portfolio</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Applications -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clock me-2"></i>Pending Loan Applications
                    </h6>
                    <a href="{{ route('loan-applications.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Loan Amount</th>
                                    <th>Purpose</th>
                                    <th>Applied Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingApplications ?? [] as $application)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user text-primary me-2"></i>
                                                {{ $application->client->full_name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>${{ number_format($application->requested_amount, 2) }}</td>
                                        <td>{{ $application->purpose ?? 'N/A' }}</td>
                                        <td>{{ $application->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-warning">{{ ucfirst($application->status) }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('loan-applications.show', $application) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Review
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No pending applications</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Loans -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Overdue Loans Requiring Attention
                    </h6>
                    <a href="{{ route('collections.index') }}" class="btn btn-sm btn-danger">Manage Collections</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Loan Amount</th>
                                    <th>Overdue Amount</th>
                                    <th>Days Overdue</th>
                                    <th>Last Payment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($overdueLoans ?? [] as $loan)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user text-danger me-2"></i>
                                                {{ $loan->client->full_name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>${{ number_format($loan->principal_amount, 2) }}</td>
                                        <td>${{ number_format($loan->outstanding_balance, 2) }}</td>
                                        <td>
                                            <span class="badge bg-danger">
                                                {{ $loan->due_date ? now()->diffInDays($loan->due_date) : 0 }} days
                                            </span>
                                        </td>
                                        <td>{{ $loan->last_payment_date ? $loan->last_payment_date->format('M d, Y') : 'Never' }}</td>
                                        <td>
                                            <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="{{ route('collections.create') }}" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-phone"></i> Contact
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No overdue loans</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- My Performance Metrics -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>My Loan Portfolio
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="myPortfolioChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>Monthly Performance
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyPerformanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('loan-applications.create') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-plus me-2"></i>New Application
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('clients.create') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-user-plus me-2"></i>Add Client
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('collections.index') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-credit-card me-2"></i>Collections
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('reports.index') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-chart-bar me-2"></i>My Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row mb-4">
        <div class="col-12">
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
                                    <th>Client</th>
                                    <th>Amount</th>
                                    <th>Status</th>
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
                                        <td>
                                            <span class="badge bg-success">Completed</span>
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
    </div>
</div>
@endsection

@section('scripts')
<script>
// My Portfolio Chart
const myPortfolioCtx = document.getElementById('myPortfolioChart').getContext('2d');
const myPortfolioChart = new Chart(myPortfolioCtx, {
    type: 'doughnut',
    data: {
        labels: ['Active Loans', 'Overdue Loans', 'Completed Loans'],
        datasets: [{
            data: [
                {{ $loanStats['active_loans'] ?? 0 }},
                {{ $loanStats['overdue_loans'] ?? 0 }},
                {{ $loanStats['completed_loans'] ?? 0 }}
            ],
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)'
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

// Monthly Performance Chart
const monthlyPerformanceCtx = document.getElementById('monthlyPerformanceChart').getContext('2d');
const monthlyPerformanceChart = new Chart(monthlyPerformanceCtx, {
    type: 'line',
    data: {
        labels: @json($monthlyTrends->pluck('month')),
        datasets: [{
            label: 'Loans Disbursed',
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
