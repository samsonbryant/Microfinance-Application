@extends('layouts.app')

@section('title', 'Approval Workflows')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-tasks text-primary me-2"></i>Approval Workflows
            </h1>
            <p class="text-muted mb-0">Multi-level loan approval management</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('approval-workflows.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>New Workflow
            </a>
            <button type="button" class="btn btn-info" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i>
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
        <div class="col-md-4">
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
        <div class="col-md-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['approved'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['rejected'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-times-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Approval Workflows</h6>
        </div>
        <div class="card-body">
            @if($workflows->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Loan</th>
                                <th>Client</th>
                                <th>Level</th>
                                <th>Approver</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workflows as $workflow)
                            <tr>
                                <td><strong>{{ $workflow->loan->loan_number }}</strong></td>
                                <td>{{ $workflow->loan->client->full_name ?? 'N/A' }}</td>
                                <td><span class="badge bg-primary">Level {{ $workflow->level }}</span></td>
                                <td>{{ $workflow->approver->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $workflow->status === 'approved' ? 'success' : ($workflow->status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($workflow->status) }}
                                    </span>
                                </td>
                                <td><small>{{ $workflow->created_at->format('M d, Y') }}</small></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('approval-workflows.show', $workflow) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                        @if($workflow->status === 'pending' && $workflow->approver_id === auth()->id())
                                            <a href="{{ route('approval-workflows.edit', $workflow) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $workflows->links() }}
            @else
                <div class="text-center py-5">
                    <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Approval Workflows</h5>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
