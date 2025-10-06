<!-- Loan Officer Dashboard -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="fas fa-hand-holding-usd me-2"></i>Loan Officer Dashboard</h2>
            <div class="text-muted">
                <i class="fas fa-calendar me-1"></i>
                {{ now()->format('l, F j, Y') }}
            </div>
        </div>
    </div>
</div>

<!-- Loan Officer Statistics -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value">{{ $stats['total_clients'] ?? 0 }}</div>
                <div class="stat-label">My Clients</div>
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
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">${{ number_format($stats['total_portfolio'] ?? 0, 0) }}</div>
                <div class="stat-label">My Portfolio</div>
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
            </div>
        </div>
    </div>
</div>

<!-- Loan Performance Chart -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-line me-2"></i>Loan Performance
                </h6>
            </div>
            <div class="card-body">
                <canvas id="loanPerformanceChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-tasks me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('clients.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>New Client
                    </a>
                    <a href="{{ route('loans.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>New Loan
                    </a>
                    <a href="{{ route('collections.index') }}" class="btn btn-warning">
                        <i class="fas fa-credit-card me-2"></i>Collections
                    </a>
                    <a href="{{ route('reports.index') }}" class="btn btn-info">
                        <i class="fas fa-chart-bar me-2"></i>Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Loan Applications -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>Recent Loan Applications
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Amount</th>
                                <th>Purpose</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($recentActivities) && is_array($recentActivities))
                                @forelse($recentActivities as $activityType => $activities)
                                    @if(is_iterable($activities))
                                        @foreach($activities as $activity)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                            {{ substr($activity->client->full_name ?? $activity->causer->name ?? 'N', 0, 1) }}
                                                        </div>
                                                        {{ $activity->client->full_name ?? $activity->causer->name ?? 'N/A' }}
                                                    </div>
                                                </td>
                                                <td>${{ number_format($activity->amount ?? 0, 2) }}</td>
                                                <td>{{ $activity->description ?? $activity->event ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge bg-warning">Pending</span>
                                                </td>
                                                <td>{{ isset($activity->created_at) ? $activity->created_at->format('M d, Y') : now()->format('M d, Y') }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="viewApplication({{ $activity->id ?? 0 }})">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-success" onclick="approveApplication({{ $activity->id ?? 0 }})">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No recent loan applications found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            @else
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No recent loan applications found</p>
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

@section('scripts')
<script>
// Loan Performance Chart
const loanCtx = document.getElementById('loanPerformanceChart').getContext('2d');
const loanPerformanceChart = new Chart(loanCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Loans Disbursed',
            data: [5, 8, 12, 15, 18, 22],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Collections',
            data: [4, 7, 10, 13, 16, 20],
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
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

function viewApplication(applicationId) {
    alert('View application: ' + applicationId);
}

function approveApplication(applicationId) {
    if (confirm('Are you sure you want to approve this application?')) {
        alert('Application approved: ' + applicationId);
    }
}
</script>
@endsection