@extends('layouts.app')

@section('title', 'Communication Logs')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-comments text-primary me-2"></i>Communication Logs
            </h1>
            <p class="text-muted mb-0">Track all client communications and interactions</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('communication-logs.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Log Communication
            </a>
            <button type="button" class="btn btn-info" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_communications'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-comments fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['today'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calendar-day fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">This Week</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['this_week'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calendar-week fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">This Month</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['this_month'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calendar-alt fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i>Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('communication-logs.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="client_id" class="form-label">Client</label>
                        <select class="form-select" id="client_id" name="client_id">
                            <option value="">All Clients</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="overdue_notification" {{ request('type') === 'overdue_notification' ? 'selected' : '' }}>Overdue Notification</option>
                            <option value="payment_reminder" {{ request('type') === 'payment_reminder' ? 'selected' : '' }}>Payment Reminder</option>
                            <option value="general" {{ request('type') === 'general' ? 'selected' : '' }}>General</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                            <a href="{{ route('communication-logs.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Communication History</h6>
        </div>
        <div class="card-body">
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Client</th>
                                <th>Type</th>
                                <th>Channel</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Sent By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                            <tr>
                                <td><small>{{ $log->sent_at ? $log->sent_at->format('M d, Y H:i') : 'N/A' }}</small></td>
                                <td>
                                    <strong>{{ $log->client->full_name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $log->client->phone ?? '' }}</small>
                                </td>
                                <td><span class="badge bg-info">{{ $log->getTypeText() }}</span></td>
                                <td><span class="badge bg-secondary">{{ $log->getChannelText() }}</span></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $log->message }}">
                                        {{ $log->message }}
                                    </div>
                                </td>
                                <td><span class="badge bg-{{ $log->getStatusBadgeClass() }}">{{ ucfirst($log->status) }}</span></td>
                                <td>{{ $log->sentBy->name ?? 'System' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('communication-logs.show', $log) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('communication-logs.edit', $log) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $logs->links() }}
            @else
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Communication Logs</h5>
                    <p class="text-muted">No client communications have been logged yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-refresh every 30 seconds
setInterval(function() {
    location.reload();
}, 30000);
</script>
@endpush
@endsection
