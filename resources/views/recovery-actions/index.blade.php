@extends('layouts.app')

@section('title', 'Recovery Actions')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-gavel text-danger me-2"></i>Recovery Actions
            </h1>
            <p class="text-muted mb-0">Manage loan recovery and collections</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('recovery-actions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>New Action
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">In Progress</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['in_progress'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-spinner fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Recovered</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($stats['total_recovered'], 0) }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recovery Actions Log</h6>
        </div>
        <div class="card-body">
            @if($actions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Client/Loan</th>
                                <th>Action Type</th>
                                <th>Performed By</th>
                                <th>Status</th>
                                <th>Outcome</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($actions as $action)
                            <tr>
                                <td><small>{{ $action->action_date->format('M d, Y') }}</small></td>
                                <td>
                                    @if($action->collection && $action->collection->loan)
                                        <strong>{{ $action->collection->loan->client->full_name ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $action->collection->loan->loan_number }}</small>
                                    @else
                                        <span class="text-muted">No loan data</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-info">{{ $action->getActionTypeText() }}</span></td>
                                <td>{{ $action->performedBy->name ?? 'N/A' }}</td>
                                <td><span class="badge bg-{{ $action->getStatusBadgeClass() }}">{{ ucfirst($action->status) }}</span></td>
                                <td>{{ $action->outcome ?? 'Pending' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('recovery-actions.show', $action) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($action->status !== 'completed')
                                            <a href="{{ route('recovery-actions.edit', $action) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $actions->links() }}
            @else
                <div class="text-center py-5">
                    <i class="fas fa-gavel fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Recovery Actions</h5>
                    <p class="text-muted">No recovery actions have been initiated yet.</p>
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
