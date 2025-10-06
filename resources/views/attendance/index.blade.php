@extends('layouts.app')

@section('title', 'Attendance Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-clock me-2"></i>Attendance Management</h4>
                <div class="btn-group">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">
                        <i class="fas fa-check me-2"></i>Mark Attendance
                    </button>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#attendanceReportModal">
                        <i class="fas fa-chart-bar me-2"></i>Generate Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value">15</div>
                    <div class="stat-label">Present Today</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-value">92.5%</div>
                    <div class="stat-label">Attendance Rate</div>
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-value">3</div>
                    <div class="stat-label">Absent Today</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>Today's Attendance
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Staff Member</th>
                                    <th>Department</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Status</th>
                                    <th>Hours Worked</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No attendance records found</p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">
                                            Mark First Attendance
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

<!-- Mark Attendance Modal -->
<div class="modal fade" id="markAttendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="staff_member" class="form-label">Staff Member</label>
                        <select class="form-select" id="staff_member">
                            <option value="">Select Staff Member</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="check_in" class="form-label">Check In Time</label>
                                <input type="datetime-local" class="form-control" id="check_in">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="check_out" class="form-label">Check Out Time</label>
                                <input type="datetime-local" class="form-control" id="check_out">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" rows="3" placeholder="Any additional notes..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Mark Attendance</button>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Report Modal -->
<div class="modal fade" id="attendanceReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Attendance Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="report_start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="report_start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="report_end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="report_end_date">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="report_department" class="form-label">Department (Optional)</label>
                        <select class="form-select" id="report_department">
                            <option value="">All Departments</option>
                            <option value="admin">Administration</option>
                            <option value="hr">Human Resources</option>
                            <option value="finance">Finance</option>
                            <option value="operations">Operations</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Generate Report</button>
            </div>
        </div>
    </div>
</div>
@endsection
