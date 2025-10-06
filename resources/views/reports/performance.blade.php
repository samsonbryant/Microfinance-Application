@extends('layouts.app')

@section('title', 'Performance Report')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-chart-line me-2"></i>Performance Report</h4>
                <div class="btn-group">
                    <a href="{{ route('reports.export-excel', 'performance') }}" class="btn btn-outline-success">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </a>
                    <a href="{{ route('reports.export-pdf', 'performance') }}" class="btn btn-outline-danger">
                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-target"></i>
                    </div>
                    <div class="stat-value">{{ $achievementRates['loans_disbursed'] ?? 0 }}%</div>
                    <div class="stat-label">Loans Disbursed</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-value">{{ $achievementRates['collections'] ?? 0 }}%</div>
                    <div class="stat-label">Collections</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value">{{ $achievementRates['new_clients'] ?? 0 }}%</div>
                    <div class="stat-label">New Clients</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="stat-value">92%</div>
                    <div class="stat-label">Overall Performance</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i>Monthly Targets vs Achievement
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="targetsChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>Performance Distribution
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="performanceDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Loan Officers Performance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users me-2"></i>Loan Officers Performance
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Officer</th>
                                    <th>Loans Processed</th>
                                    <th>Clients Served</th>
                                    <th>Total Amount</th>
                                    <th>Success Rate</th>
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($performance['loan_officers'] ?? [] as $officer)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    {{ substr($officer->name, 0, 1) }}
                                                </div>
                                                {{ $officer->name }}
                                            </div>
                                        </td>
                                        <td>{{ $officer->loans_count ?? 0 }}</td>
                                        <td>{{ $officer->clients_count ?? 0 }}</td>
                                        <td>${{ number_format(0, 2) }}</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 85%">
                                                    85%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="rating">
                                                <i class="fas fa-star text-warning"></i>
                                                <i class="fas fa-star text-warning"></i>
                                                <i class="fas fa-star text-warning"></i>
                                                <i class="fas fa-star text-warning"></i>
                                                <i class="fas fa-star text-muted"></i>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No loan officers found</p>
                                        </td>
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
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building me-2"></i>Branch Performance
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Branch</th>
                                    <th>Loans</th>
                                    <th>Clients</th>
                                    <th>Total Portfolio</th>
                                    <th>Performance Score</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branchPerformance ?? [] as $branch)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-building text-primary me-2"></i>
                                                {{ $branch['name'] }}
                                            </div>
                                        </td>
                                        <td>{{ $branch['loans_count'] ?? 0 }}</td>
                                        <td>{{ $branch['clients_count'] ?? 0 }}</td>
                                        <td>${{ number_format($branch['total_portfolio'] ?? 0, 2) }}</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 88%">
                                                    88%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Excellent</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No branch data found</p>
                                        </td>
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
// Targets vs Achievement Chart
const targetsCtx = document.getElementById('targetsChart').getContext('2d');
const targetsChart = new Chart(targetsCtx, {
    type: 'bar',
    data: {
        labels: ['Loans Disbursed', 'Collections', 'New Clients'],
        datasets: [{
            label: 'Target',
            data: [50, 100000, 25],
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }, {
            label: 'Achievement',
            data: [42, 92000, 19],
            backgroundColor: 'rgba(75, 192, 192, 0.8)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Performance Distribution Chart
const distributionCtx = document.getElementById('performanceDistributionChart').getContext('2d');
const performanceDistributionChart = new Chart(distributionCtx, {
    type: 'doughnut',
    data: {
        labels: ['Excellent', 'Good', 'Average', 'Poor'],
        datasets: [{
            data: [5, 8, 3, 1],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(0, 123, 255, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)'
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(0, 123, 255, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(220, 53, 69, 1)'
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
