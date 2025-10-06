<!-- General Manager Dashboard -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="fas fa-chart-line me-2"></i>General Manager Dashboard</h2>
            <div class="text-muted">
                <i class="fas fa-calendar me-1"></i>
                {{ now()->format('l, F j, Y') }}
            </div>
        </div>
    </div>
</div>

<!-- Management Statistics -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-value">{{ $stats['total_branches'] ?? 0 }}</div>
                <div class="stat-label">Total Branches</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value">{{ $stats['total_clients'] ?? 0 }}</div>
                <div class="stat-label">Total Clients</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">${{ number_format($stats['total_loan_portfolio'] ?? 0, 0) }}</div>
                <div class="stat-label">Total Portfolio</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-value">{{ number_format($parPercentage ?? 0, 1) }}%</div>
                <div class="stat-label">PAR Rate</div>
            </div>
        </div>
    </div>
</div>

<!-- Branch Performance Overview -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-bar me-2"></i>Branch Performance Overview
                </h6>
            </div>
            <div class="card-body">
                <canvas id="branchOverviewChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-tasks me-2"></i>Management Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('branches.index') }}" class="btn btn-primary">
                        <i class="fas fa-building me-2"></i>Manage Branches
                    </a>
                    <a href="{{ route('reports.index') }}" class="btn btn-success">
                        <i class="fas fa-chart-bar me-2"></i>View Reports
                    </a>
                    <a href="{{ route('approval-workflows.index') }}" class="btn btn-warning">
                        <i class="fas fa-check-circle me-2"></i>Approvals
                    </a>
                    <a href="{{ route('collections.index') }}" class="btn btn-info">
                        <i class="fas fa-credit-card me-2"></i>Collections
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Branch Performance Table -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-table me-2"></i>Branch Performance Details
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Branch</th>
                                <th>Clients</th>
                                <th>Loans</th>
                                <th>Portfolio</th>
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
                                    <td>{{ $branch->loans_count ?? 0 }}</td>
                                    <td>${{ number_format($branch->loan_portfolio ?? 0, 2) }}</td>
                                    <td>{{ number_format($branch->par_percentage ?? 0, 1) }}%</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 88%">
                                                88%
                                            </div>
                                        </div>
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

@section('scripts')
<script>
// Branch Overview Chart
const overviewCtx = document.getElementById('branchOverviewChart').getContext('2d');
const branchOverviewChart = new Chart(overviewCtx, {
    type: 'bar',
    data: {
        labels: ['Branch A', 'Branch B', 'Branch C', 'Branch D'],
        datasets: [{
            label: 'Loan Portfolio ($)',
            data: [120000, 150000, 180000, 200000],
            backgroundColor: 'rgba(75, 192, 192, 0.8)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }, {
            label: 'Collections ($)',
            data: [100000, 130000, 160000, 180000],
            backgroundColor: 'rgba(255, 99, 132, 0.8)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
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
