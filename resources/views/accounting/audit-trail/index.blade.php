@extends('layouts.app')

@section('title', 'Audit Trail - Microbook-G5')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-history me-2"></i>Audit Trail - Microbook-G5</h4>
                <div class="btn-group">
                    <button type="button" class="btn btn-success" onclick="exportAuditTrail()">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                    <a href="{{ route('accounting.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $statistics['total_activities'] ?? 0 }}</h4>
                            <p class="mb-0">Total Activities</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $statistics['unique_users'] ?? 0 }}</h4>
                            <p class="mb-0">Active Users</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $statistics['activities_by_type']->count() ?? 0 }}</h4>
                            <p class="mb-0">Activity Types</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tags fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $statistics['daily_activity']->max() ?? 0 }}</h4>
                            <p class="mb-0">Peak Daily Activity</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter me-2"></i>Filters
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('accounting.audit-trail') }}">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="{{ $startDate }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="{{ $endDate }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">User</label>
                                    <select class="form-select" id="user_id" name="user_id">
                                        <option value="">All Users</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="log_name" class="form-label">Activity Type</label>
                                    <select class="form-select" id="log_name" name="log_name">
                                        <option value="">All Types</option>
                                        @foreach($logNames as $logName)
                                            <option value="{{ $logName }}" {{ $logName == $logName ? 'selected' : '' }}>
                                                {{ ucwords(str_replace('_', ' ', $logName)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-2"></i>Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Trail Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>Audit Trail
                    </h6>
                </div>
                <div class="card-body">
                    @if($auditTrail->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Model</th>
                                        <th>Details</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($auditTrail as $activity)
                                        <tr>
                                            <td>
                                                <small>{{ $activity->created_at->format('M d, Y H:i:s') }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $activity->causer->name ?? 'System' }}</strong>
                                                        @if($activity->causer)
                                                            <br><small class="text-muted">{{ $activity->causer->email }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $this->getActivityBadgeClass($activity->log_name) }}">
                                                    {{ ucwords(str_replace('_', ' ', $activity->log_name)) }}
                                                </span>
                                                <br><small>{{ $activity->description }}</small>
                                            </td>
                                            <td>
                                                @if($activity->subject)
                                                    <strong>{{ class_basename($activity->subject_type) }}</strong>
                                                    <br><small class="text-muted">ID: {{ $activity->subject_id }}</small>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($activity->properties && count($activity->properties) > 0)
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#detailsModal{{ $activity->id }}">
                                                        <i class="fas fa-info-circle"></i> View
                                                    </button>
                                                @else
                                                    <span class="text-muted">No details</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('accounting.audit-trail.show', $activity) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>

                                        <!-- Details Modal -->
                                        @if($activity->properties && count($activity->properties) > 0)
                                            <div class="modal fade" id="detailsModal{{ $activity->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Activity Details</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <h6>Properties:</h6>
                                                            <pre class="bg-light p-3 rounded">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $auditTrail->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No audit trail entries found</h5>
                            <p class="text-muted">No activities match your current filters.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function exportAuditTrail() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    const url = new URL('{{ route("accounting.audit-trail.export") }}', window.location.origin);
    url.searchParams.append('start_date', startDate);
    url.searchParams.append('end_date', endDate);
    url.searchParams.append('format', 'csv');
    
    window.open(url.toString(), '_blank');
}

// Real-time updates (optional)
setInterval(function() {
    // Implement real-time updates if needed
}, 30000); // Check every 30 seconds
</script>
@endsection
