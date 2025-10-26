@extends('layouts.app')

@section('title', 'Reconciliations - Microbook-G5')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-balance-scale me-2"></i>Reconciliations - Microbook-G5</h4>
                <div class="btn-group">
                    @can('manage_reconciliations')
                        <a href="{{ route('accounting.reconciliations.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>New Reconciliation
                        </a>
                    @endcan
                    <a href="{{ route('accounting.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
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
                    <form method="GET" action="{{ route('accounting.reconciliations') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type</label>
                                    <select class="form-select" id="type" name="type">
                                        <option value="">All Types</option>
                                        @foreach($reconciliationTypes as $key => $label)
                                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">All Statuses</option>
                                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="{{ request('end_date') }}">
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

    <!-- Reconciliations Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>Reconciliations
                    </h6>
                </div>
                <div class="card-body">
                    @if($reconciliations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Reconciliation #</th>
                                        <th>Type</th>
                                        <th>Account</th>
                                        <th>Date</th>
                                        <th>System Balance</th>
                                        <th>Actual Balance</th>
                                        <th>Variance</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reconciliations as $reconciliation)
                                        <tr>
                                            <td>
                                                <code>{{ $reconciliation->reconciliation_number }}</code>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $reconciliationTypes[$reconciliation->type] ?? $reconciliation->type }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>{{ $reconciliation->account->code }}</strong> - {{ $reconciliation->account->name }}
                                            </td>
                                            <td>{{ $reconciliation->reconciliation_date->format('M d, Y') }}</td>
                                            <td class="text-end">{{ $reconciliation->getFormattedSystemBalance() }}</td>
                                            <td class="text-end">{{ $reconciliation->getFormattedActualBalance() }}</td>
                                            <td class="text-end {{ $reconciliation->getVarianceClass() }}">
                                                {{ $reconciliation->getFormattedVariance() }}
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $reconciliation->getStatusBadgeClass() }}">
                                                    {{ ucfirst(str_replace('_', ' ', $reconciliation->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $reconciliation->user->name }}
                                                <br><small class="text-muted">{{ $reconciliation->created_at->format('M d, Y') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('accounting.reconciliations.show', $reconciliation) }}" 
                                                       class="btn btn-outline-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($reconciliation->isDraft() && Auth::user()->can('manage_reconciliations'))
                                                        <form action="{{ route('accounting.reconciliations.start', $reconciliation) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-primary" 
                                                                    title="Start Reconciliation">
                                                                <i class="fas fa-play"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    @if($reconciliation->isCompleted() && Auth::user()->can('approve_reconciliations'))
                                                        <form action="{{ route('accounting.reconciliations.approve', $reconciliation) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-success" 
                                                                    title="Approve"
                                                                    onclick="return confirm('Are you sure you want to approve this reconciliation?')">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $reconciliations->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-balance-scale fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No reconciliations found</h5>
                            <p class="text-muted">Start by creating your first reconciliation.</p>
                            @can('manage_reconciliations')
                                <a href="{{ route('accounting.reconciliations.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create First Reconciliation
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
