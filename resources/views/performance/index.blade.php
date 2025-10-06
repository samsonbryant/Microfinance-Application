@extends('layouts.app')

@section('title', 'Performance Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-chart-line me-2"></i>Performance Management</h4>
                <div class="btn-group">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newReviewModal">
                        <i class="fas fa-plus me-2"></i>New Performance Review
                    </button>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#performanceReportModal">
                        <i class="fas fa-chart-bar me-2"></i>Generate Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-value">5</div>
                    <div class="stat-label">Excellent Performers</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-thumbs-up"></i>
                    </div>
                    <div class="stat-value">8</div>
                    <div class="stat-label">Good Performers</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-minus"></i>
                    </div>
                    <div class="stat-value">3</div>
                    <div class="stat-label">Average Performers</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-danger text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value">1</div>
                    <div class="stat-label">Poor Performers</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>Performance Reviews
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Staff Member</th>
                                    <th>Department</th>
                                    <th>Review Period</th>
                                    <th>Overall Score</th>
                                    <th>Rating</th>
                                    <th>Reviewer</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No performance reviews found</p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newReviewModal">
                                            Create First Performance Review
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

<!-- New Performance Review Modal -->
<div class="modal fade" id="newReviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Performance Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="staff_member" class="form-label">Staff Member</label>
                                <select class="form-select" id="staff_member">
                                    <option value="">Select Staff Member</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reviewer" class="form-label">Reviewer</label>
                                <select class="form-select" id="reviewer">
                                    <option value="">Select Reviewer</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="review_period_start" class="form-label">Review Period Start</label>
                                <input type="date" class="form-control" id="review_period_start">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="review_period_end" class="form-label">Review Period End</label>
                                <input type="date" class="form-control" id="review_period_end">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="overall_score" class="form-label">Overall Score (1-100)</label>
                        <input type="number" class="form-control" id="overall_score" min="1" max="100">
                    </div>
                    <div class="mb-3">
                        <label for="comments" class="form-label">Comments</label>
                        <textarea class="form-control" id="comments" rows="4" placeholder="Enter performance review comments..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Create Review</button>
            </div>
        </div>
    </div>
</div>

<!-- Performance Report Modal -->
<div class="modal fade" id="performanceReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Performance Report</h5>
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
                    <div class="mb-3">
                        <label for="report_format" class="form-label">Report Format</label>
                        <select class="form-select" id="report_format">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
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
