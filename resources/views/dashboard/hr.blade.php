<!-- HR Dashboard -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="fas fa-users-cog me-2"></i>HR Dashboard</h2>
            <div class="text-muted">
                <i class="fas fa-calendar me-1"></i>
                {{ now()->format('l, F j, Y') }}
            </div>
        </div>
    </div>
</div>

<!-- HR Statistics -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value">{{ $stats['total_users'] ?? 0 }}</div>
                <div class="stat-label">Total Staff</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-value">{{ $stats['active_users'] ?? 0 }}</div>
                <div class="stat-label">Active Staff</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value">15</div>
                <div class="stat-label">Present Today</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-value">2</div>
                <div class="stat-label">Late Arrivals</div>
            </div>
        </div>
    </div>
</div>

<!-- Staff Performance Chart -->
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-bar me-2"></i>Staff Performance
                </h6>
            </div>
            <div class="card-body">
                <canvas id="staffPerformanceChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-tasks me-2"></i>HR Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('staff.index') }}" class="btn btn-primary">
                        <i class="fas fa-users me-2"></i>Manage Staff
                    </a>
                    <a href="{{ route('payrolls.index') }}" class="btn btn-success">
                        <i class="fas fa-money-bill me-2"></i>Payroll
                    </a>
                    <a href="{{ route('attendance.index') }}" class="btn btn-warning">
                        <i class="fas fa-clock me-2"></i>Attendance
                    </a>
                    <a href="{{ route('performance.index') }}" class="btn btn-info">
                        <i class="fas fa-chart-line me-2"></i>Performance
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent HR Activities -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i>Recent HR Activities
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Activity</th>
                                <th>Staff Member</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2025-10-06</td>
                                <td>Performance Review</td>
                                <td>John Doe</td>
                                <td>Review</td>
                                <td><span class="badge bg-success">Completed</span></td>
                            </tr>
                            <tr>
                                <td>2025-10-05</td>
                                <td>Payroll Processing</td>
                                <td>All Staff</td>
                                <td>Payroll</td>
                                <td><span class="badge bg-success">Completed</span></td>
                            </tr>
                            <tr>
                                <td>2025-10-04</td>
                                <td>New Employee Onboarding</td>
                                <td>Jane Smith</td>
                                <td>Onboarding</td>
                                <td><span class="badge bg-warning">In Progress</span></td>
                            </tr>
                            <tr>
                                <td>2025-10-03</td>
                                <td>Attendance Review</td>
                                <td>Branch A Staff</td>
                                <td>Attendance</td>
                                <td><span class="badge bg-info">Pending</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
// Staff Performance Chart
const staffCtx = document.getElementById('staffPerformanceChart').getContext('2d');
const staffPerformanceChart = new Chart(staffCtx, {
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