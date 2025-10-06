@extends('layouts.app')

@section('title', 'System Health')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-heartbeat me-2"></i>System Health</h4>
                <button class="btn btn-primary" onclick="refreshSystemHealth()">
                    <i class="fas fa-sync-alt me-2"></i>Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- System Status Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-server"></i>
                    </div>
                    <div class="stat-value">Online</div>
                    <div class="stat-label">Server Status</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="stat-value">Healthy</div>
                    <div class="stat-label">Database</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-memory"></i>
                    </div>
                    <div class="stat-value">75%</div>
                    <div class="stat-label">Memory Usage</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-hdd"></i>
                    </div>
                    <div class="stat-value">45%</div>
                    <div class="stat-label">Disk Usage</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- System Metrics -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>System Metrics
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="systemMetricsChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- System Alerts -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-exclamation-triangle me-2"></i>System Alerts
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Memory Usage High:</strong> Consider optimizing memory usage.
                    </div>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Backup Scheduled:</strong> Next backup in 2 hours.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>System Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Application Version:</strong></td>
                                    <td>1.0.0</td>
                                </tr>
                                <tr>
                                    <td><strong>Laravel Version:</strong></td>
                                    <td>11.x</td>
                                </tr>
                                <tr>
                                    <td><strong>PHP Version:</strong></td>
                                    <td>8.3.25</td>
                                </tr>
                                <tr>
                                    <td><strong>Database:</strong></td>
                                    <td>SQLite</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Server Uptime:</strong></td>
                                    <td>5 days, 12 hours</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Backup:</strong></td>
                                    <td>2 hours ago</td>
                                </tr>
                                <tr>
                                    <td><strong>Active Users:</strong></td>
                                    <td>12</td>
                                </tr>
                                <tr>
                                    <td><strong>Queue Jobs:</strong></td>
                                    <td>3 pending</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function refreshSystemHealth() {
    // Simulate refreshing system health data
    location.reload();
}

// System Metrics Chart
const ctx = document.getElementById('systemMetricsChart').getContext('2d');
const systemMetricsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
        datasets: [{
            label: 'CPU Usage (%)',
            data: [25, 30, 45, 60, 55, 40],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Memory Usage (%)',
            data: [40, 45, 50, 65, 70, 75],
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});
</script>
@endsection
