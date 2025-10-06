@extends('layouts.app')

@section('title', 'System Maintenance')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-wrench me-2"></i>System Maintenance</h4>
                <div class="btn-group">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#maintenanceModal">
                        <i class="fas fa-tools me-2"></i>Schedule Maintenance
                    </button>
                    <button class="btn btn-outline-success" onclick="runMaintenance()">
                        <i class="fas fa-play me-2"></i>Run Maintenance
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Status Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value">Online</div>
                    <div class="stat-label">System Status</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value">2h 30m</div>
                    <div class="stat-label">Last Maintenance</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-value">Tomorrow</div>
                    <div class="stat-label">Next Scheduled</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-value">5</div>
                    <div class="stat-label">Pending Tasks</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Maintenance Tasks -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>Maintenance Tasks
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Status</th>
                                    <th>Last Run</th>
                                    <th>Next Run</th>
                                    <th>Duration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-database text-primary me-2"></i>
                                            Database Optimization
                                        </div>
                                    </td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>2 hours ago</td>
                                    <td>Daily at 2:00 AM</td>
                                    <td>15 minutes</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="runTask('db_optimization')">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-trash text-warning me-2"></i>
                                            Log Cleanup
                                        </div>
                                    </td>
                                    <td><span class="badge bg-warning">Pending</span></td>
                                    <td>1 day ago</td>
                                    <td>Weekly on Sunday</td>
                                    <td>5 minutes</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="runTask('log_cleanup')">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-archive text-info me-2"></i>
                                            Backup Creation
                                        </div>
                                    </td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>6 hours ago</td>
                                    <td>Every 6 hours</td>
                                    <td>30 minutes</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="runTask('backup')">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-chart-line text-success me-2"></i>
                                            Performance Analysis
                                        </div>
                                    </td>
                                    <td><span class="badge bg-info">Running</span></td>
                                    <td>In progress</td>
                                    <td>Daily at 3:00 AM</td>
                                    <td>45 minutes</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger" onclick="stopTask('performance_analysis')">
                                            <i class="fas fa-stop"></i>
                                        </button>
                                    </td>
                                </tr>
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
                        <button class="btn btn-outline-primary" onclick="clearCache()">
                            <i class="fas fa-broom me-2"></i>Clear Cache
                        </button>
                        <button class="btn btn-outline-success" onclick="optimizeDatabase()">
                            <i class="fas fa-database me-2"></i>Optimize Database
                        </button>
                        <button class="btn btn-outline-warning" onclick="cleanLogs()">
                            <i class="fas fa-trash me-2"></i>Clean Logs
                        </button>
                        <button class="btn btn-outline-info" onclick="createBackup()">
                            <i class="fas fa-archive me-2"></i>Create Backup
                        </button>
                        <button class="btn btn-outline-danger" onclick="restartServices()">
                            <i class="fas fa-redo me-2"></i>Restart Services
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Maintenance Modal -->
<div class="modal fade" id="maintenanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Maintenance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="maintenance_type" class="form-label">Maintenance Type</label>
                                <select class="form-select" id="maintenance_type">
                                    <option value="scheduled">Scheduled Maintenance</option>
                                    <option value="emergency">Emergency Maintenance</option>
                                    <option value="update">System Update</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="maintenance_date" class="form-label">Scheduled Date</label>
                                <input type="datetime-local" class="form-control" id="maintenance_date">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="maintenance_tasks" class="form-label">Tasks to Perform</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="task_db_optimization">
                            <label class="form-check-label" for="task_db_optimization">
                                Database Optimization
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="task_log_cleanup">
                            <label class="form-check-label" for="task_log_cleanup">
                                Log Cleanup
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="task_backup">
                            <label class="form-check-label" for="task_backup">
                                Full System Backup
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="task_cache_clear">
                            <label class="form-check-label" for="task_cache_clear">
                                Cache Clear
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="maintenance_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="maintenance_notes" rows="3" placeholder="Additional notes about the maintenance..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Schedule Maintenance</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function runMaintenance() {
    if (confirm('Are you sure you want to run maintenance tasks? This may temporarily affect system performance.')) {
        alert('Maintenance tasks started successfully!');
    }
}

function runTask(taskName) {
    alert(`Running task: ${taskName}`);
}

function stopTask(taskName) {
    alert(`Stopping task: ${taskName}`);
}

function clearCache() {
    if (confirm('Clear all application cache?')) {
        alert('Cache cleared successfully!');
    }
}

function optimizeDatabase() {
    if (confirm('Optimize database? This may take several minutes.')) {
        alert('Database optimization started!');
    }
}

function cleanLogs() {
    if (confirm('Clean old log files?')) {
        alert('Logs cleaned successfully!');
    }
}

function createBackup() {
    if (confirm('Create a full system backup?')) {
        alert('Backup creation started!');
    }
}

function restartServices() {
    if (confirm('Restart all services? This will cause temporary downtime.')) {
        alert('Services restart initiated!');
    }
}
</script>
@endsection
