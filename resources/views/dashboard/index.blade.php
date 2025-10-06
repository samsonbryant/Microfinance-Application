@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Welcome Message -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info border-0">
            <i class="fas fa-info-circle me-2"></i>
            Welcome back, <strong>{{ auth()->user()->name }}</strong>! Here's what's happening with your microfinance operations.
        </div>
    </div>
</div>

<!-- Live Dashboard Metrics -->
@livewire('dashboard-metrics')

<!-- Role-specific content -->
@if(auth()->user()->hasRole('admin'))
    @include('dashboard.admin')
@elseif(auth()->user()->hasRole('general_manager'))
    @include('dashboard.general-manager')
@elseif(auth()->user()->hasRole('branch_manager'))
    @include('dashboard.branch-manager')
@elseif(auth()->user()->hasRole('loan_officer'))
    @include('dashboard.loan-officer')
@elseif(auth()->user()->hasRole('hr'))
    @include('dashboard.hr')
@elseif(auth()->user()->hasRole('borrower'))
    @include('dashboard.borrower')
@endif

    <!-- Portfolio at Risk Alert -->
    @if($portfolioAtRisk['par_percentage'] > 5)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Portfolio at Risk Alert!</strong> 
                Your PAR (Portfolio at Risk) is {{ $portfolioAtRisk['par_percentage'] }}% 
                ({{ $portfolioAtRisk['overdue_count'] }} overdue loans worth ${{ number_format($portfolioAtRisk['overdue_amount'], 2) }}).
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif

    <!-- Charts and Analytics Row -->
    <div class="row">
        <!-- Monthly Trends Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Trends</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <a class="dropdown-item" href="#" onclick="exportChart('monthly-trends')">Export Chart</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="monthlyTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Portfolio Distribution -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Loan Status Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="loanStatusChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Active
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> Overdue
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> Completed
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities Row -->
    <div class="row">
        <!-- Recent Transactions -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Client</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities['recent_transactions'] as $transaction)
                                <tr>
                                    <td>
                                        <span class="badge bg-{{ $transaction->type === 'deposit' ? 'success' : ($transaction->type === 'withdrawal' ? 'warning' : 'info') }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->client->full_name ?? 'N/A' }}</td>
                                    <td>${{ number_format($transaction->amount, 2) }}</td>
                                    <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No recent transactions</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Applications -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pending Applications</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Officer</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingApplications as $application)
                                <tr>
                                    <td>{{ $application->client->full_name }}</td>
                                    <td>${{ number_format($application->requested_amount, 2) }}</td>
                                    <td>{{ ucfirst($application->loan_type) }}</td>
                                    <td>{{ $application->loanOfficer->name }}</td>
                                    <td>{{ $application->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('loan-applications.show', $application) }}" class="btn btn-sm btn-outline-primary">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No pending applications</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Performance -->
    @if(auth()->user()->role === 'admin' && $branchPerformance && $branchPerformance->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Branch Performance Overview</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Branch</th>
                                    <th>Clients</th>
                                    <th>Active Loans</th>
                                    <th>Portfolio Value</th>
                                    <th>Savings Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branchPerformance as $branch)
                                <tr>
                                    <td>{{ $branch['name'] ?? 'N/A' }}</td>
                                    <td>{{ number_format($branch['clients_count'] ?? 0) }}</td>
                                    <td>{{ number_format($branch['active_loans'] ?? $branch['loans_count'] ?? 0) }}</td>
                                    <td>${{ number_format($branch['portfolio_value'] ?? 0, 2) }}</td>
                                    <td>${{ number_format($branch['savings_balance'] ?? 0, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Quick Action Buttons -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
    <div class="btn-group-vertical">
        <a href="{{ route('loan-applications.create') }}" class="btn btn-primary mb-2" title="New Loan Application">
            <i class="fas fa-plus"></i>
        </a>
        <a href="{{ route('clients.create') }}" class="btn btn-success mb-2" title="New Client">
            <i class="fas fa-user-plus"></i>
        </a>
        <a href="{{ route('transactions.create') }}" class="btn btn-info" title="New Transaction">
            <i class="fas fa-exchange-alt"></i>
        </a>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Trends Chart
const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
const monthlyTrendsChart = new Chart(monthlyTrendsCtx, {
    type: 'line',
    data: {
        labels: @json($monthlyTrends->pluck('month')),
        datasets: [{
            label: 'Loans Disbursed',
            data: @json($monthlyTrends->pluck('loans_disbursed')),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Loans Collected',
            data: @json($monthlyTrends->pluck('loans_collected')),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }, {
            label: 'New Clients',
            data: @json($monthlyTrends->pluck('new_clients')),
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Loan Status Distribution Chart
const loanStatusCtx = document.getElementById('loanStatusChart').getContext('2d');
const loanStatusChart = new Chart(loanStatusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Active', 'Overdue', 'Completed'],
        datasets: [{
            data: [
                {{ $loanStats['active_loans'] }},
                {{ $loanStats['overdue_loans'] }},
                {{ $stats['total_loans'] - $loanStats['active_loans'] - $loanStats['overdue_loans'] }}
            ],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(23, 162, 184, 0.8)'
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(23, 162, 184, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

function refreshDashboard() {
    location.reload();
}

function exportChart(chartType) {
    // Implementation for chart export
    console.log('Exporting chart:', chartType);
}
</script>
@endsection
