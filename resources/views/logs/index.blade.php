@extends('layouts.app')

@section('title', 'System Logs')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-file-alt me-2"></i>System Logs</h4>
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="refreshLogs()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                    <button class="btn btn-outline-danger" onclick="clearLogs()">
                        <i class="fas fa-trash me-2"></i>Clear Logs
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter me-2"></i>Log Filters
                    </h6>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="log_level" class="form-label">Log Level</label>
                                    <select class="form-select" id="log_level">
                                        <option value="">All Levels</option>
                                        <option value="error">Error</option>
                                        <option value="warning">Warning</option>
                                        <option value="info">Info</option>
                                        <option value="debug">Debug</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="date_from">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="date_to">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" class="form-control" id="search" placeholder="Search logs...">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Filter Logs
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                                    <i class="fas fa-times me-2"></i>Clear Filters
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-danger text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-value">12</div>
                    <div class="stat-label">Errors Today</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value">8</div>
                    <div class="stat-label">Warnings Today</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="stat-value">156</div>
                    <div class="stat-label">Info Messages</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value">1,234</div>
                    <div class="stat-label">Total Logs</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>System Logs
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Level</th>
                                    <th>Message</th>
                                    <th>Context</th>
                                    <th>User</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2025-10-06 15:45:22</td>
                                    <td><span class="badge bg-danger">ERROR</span></td>
                                    <td>Undefined property: stdClass::$is_active</td>
                                    <td>View: dashboard.admin</td>
                                    <td>admin@microfinance.com</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewLogDetails(1)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2025-10-06 15:44:15</td>
                                    <td><span class="badge bg-warning">WARNING</span></td>
                                    <td>Route not found: reports.financial</td>
                                    <td>Route: web</td>
                                    <td>admin@microfinance.com</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewLogDetails(2)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2025-10-06 15:43:08</td>
                                    <td><span class="badge bg-info">INFO</span></td>
                                    <td>User logged in successfully</td>
                                    <td>Auth</td>
                                    <td>admin@microfinance.com</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewLogDetails(3)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="logDetailsContent" class="bg-light p-3 rounded"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function refreshLogs() {
    location.reload();
}

function clearLogs() {
    if (confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
        // Implement clear logs functionality
        alert('Logs cleared successfully!');
        location.reload();
    }
}

function clearFilters() {
    document.getElementById('log_level').value = '';
    document.getElementById('date_from').value = '';
    document.getElementById('date_to').value = '';
    document.getElementById('search').value = '';
}

function viewLogDetails(logId) {
    // Simulate log details
    const logDetails = {
        1: {
            timestamp: '2025-10-06 15:45:22',
            level: 'ERROR',
            message: 'Undefined property: stdClass::$is_active',
            context: 'View: dashboard.admin',
            user: 'admin@microfinance.com',
            stackTrace: 'at C:\\Users\\DELL\\LoanManagementSystem\\microfinance-laravel\\storage\\framework\\views\\edc66ee506272d31602c3f7db7e2f2b5.php:276\nat Illuminate\\View\\View->render()\n...'
        },
        2: {
            timestamp: '2025-10-06 15:44:15',
            level: 'WARNING',
            message: 'Route not found: reports.financial',
            context: 'Route: web',
            user: 'admin@microfinance.com',
            stackTrace: 'at Illuminate\\Routing\\RouteCollection->match()\n...'
        },
        3: {
            timestamp: '2025-10-06 15:43:08',
            level: 'INFO',
            message: 'User logged in successfully',
            context: 'Auth',
            user: 'admin@microfinance.com',
            stackTrace: 'N/A'
        }
    };
    
    const log = logDetails[logId];
    if (log) {
        document.getElementById('logDetailsContent').textContent = JSON.stringify(log, null, 2);
        new bootstrap.Modal(document.getElementById('logDetailsModal')).show();
    }
}
</script>
@endsection
