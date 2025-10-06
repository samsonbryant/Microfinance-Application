@extends('layouts.app')

@section('title', 'Recovery Actions')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Recovery Actions</h1>
    <p class="page-subtitle">Manage legal actions and recovery processes for overdue loans.</p>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $stats['total_recovery'] }}</h4>
                        <p class="card-text">Recovery Cases</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-gavel fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">${{ number_format($stats['total_amount'], 2) }}</h4>
                        <p class="card-text">Recovery Amount</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-dollar-sign fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $stats['legal_actions'] }}</h4>
                        <p class="card-text">Legal Actions</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-balance-scale fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $stats['written_off'] }}</h4>
                        <p class="card-text">Written Off</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recovery Loans Table -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Recovery Cases</h6>
    </div>
    <div class="card-body">
        @if($recoveryLoans->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Loan #</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Days Overdue</th>
                            <th>Status</th>
                            <th>Branch</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recoveryLoans as $loan)
                        <tr>
                            <td>
                                <strong>{{ $loan->loan_number }}</strong>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $loan->client->full_name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $loan->client->client_number ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <strong class="text-danger">${{ number_format($loan->outstanding_balance, 2) }}</strong>
                            </td>
                            <td>
                                <span class="text-danger">{{ $loan->due_date->format('M d, Y') }}</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">
                                    {{ now()->diffInDays($loan->due_date) }} days
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ 
                                    $loan->status === 'legal_action' ? 'warning' : 
                                    ($loan->status === 'written_off' ? 'secondary' : 'danger') 
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $loan->status)) }}
                                </span>
                            </td>
                            <td>{{ $loan->branch->name ?? 'N/A' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($loan->status !== 'written_off')
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" 
                                                data-bs-target="#legalActionModal{{ $loan->id }}">
                                            <i class="fas fa-gavel"></i> Legal
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" 
                                                data-bs-target="#collateralModal{{ $loan->id }}">
                                            <i class="fas fa-shield-alt"></i> Collateral
                                        </button>
                                    @endif
                                    <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $recoveryLoans->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5 class="text-success">No Recovery Cases</h5>
                <p class="text-muted">No loans require recovery actions at this time.</p>
            </div>
        @endif
    </div>
</div>

<!-- Legal Action Modal -->
@foreach($recoveryLoans as $loan)
@if($loan->status !== 'written_off')
<div class="modal fade" id="legalActionModal{{ $loan->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Initiate Legal Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('recovery.legal-action', $loan) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <p class="form-control-plaintext">{{ $loan->client->full_name ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label for="action_type{{ $loan->id }}" class="form-label">Action Type</label>
                        <select class="form-select" id="action_type{{ $loan->id }}" name="action_type" required>
                            <option value="">Select Action</option>
                            <option value="demand_letter">Demand Letter</option>
                            <option value="legal_notice">Legal Notice</option>
                            <option value="court_filing">Court Filing</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description{{ $loan->id }}" class="form-label">Description</label>
                        <textarea class="form-control" id="description{{ $loan->id }}" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="expected_outcome{{ $loan->id }}" class="form-label">Expected Outcome</label>
                        <textarea class="form-control" id="expected_outcome{{ $loan->id }}" name="expected_outcome" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="estimated_cost{{ $loan->id }}" class="form-label">Estimated Cost</label>
                        <input type="number" class="form-control" id="estimated_cost{{ $loan->id }}" name="estimated_cost" step="0.01" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Initiate Legal Action</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Collateral Seizure Modal -->
<div class="modal fade" id="collateralModal{{ $loan->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Collateral Seizure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('recovery.collateral-seizure', $loan) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <p class="form-control-plaintext">{{ $loan->client->full_name ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label for="seizure_reason{{ $loan->id }}" class="form-label">Seizure Reason</label>
                        <textarea class="form-control" id="seizure_reason{{ $loan->id }}" name="seizure_reason" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="collateral_value{{ $loan->id }}" class="form-label">Collateral Value</label>
                                <input type="number" class="form-control" id="collateral_value{{ $loan->id }}" name="collateral_value" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="seizure_date{{ $loan->id }}" class="form-label">Seizure Date</label>
                                <input type="date" class="form-control" id="seizure_date{{ $loan->id }}" name="seizure_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="storage_location{{ $loan->id }}" class="form-label">Storage Location</label>
                        <input type="text" class="form-control" id="storage_location{{ $loan->id }}" name="storage_location" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Initiate Seizure</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
@endsection
