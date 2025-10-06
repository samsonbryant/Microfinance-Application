@extends('layouts.app')

@section('title', 'Collections')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Collections</h1>
    <p class="page-subtitle">Manage overdue loans and collection activities.</p>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $stats['total_overdue'] }}</h4>
                        <p class="card-text">Overdue Loans</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
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
                        <p class="card-text">Overdue Amount</p>
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
                        <h4 class="card-title">{{ round($stats['avg_days_overdue']) }}</h4>
                        <p class="card-text">Avg Days Overdue</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $stats['collections_today'] }}</h4>
                        <p class="card-text">Collections Today</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-phone fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overdue Loans Table -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Overdue Loans</h6>
    </div>
    <div class="card-body">
        @if($overdueLoans->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Loan #</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Days Overdue</th>
                            <th>Branch</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overdueLoans as $loan)
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
                            <td>{{ $loan->branch->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-danger">Overdue</span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                            data-bs-target="#contactModal{{ $loan->id }}">
                                        <i class="fas fa-phone"></i> Contact
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" 
                                            data-bs-target="#escalateModal{{ $loan->id }}">
                                        <i class="fas fa-arrow-up"></i> Escalate
                                    </button>
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
                {{ $overdueLoans->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5 class="text-success">No Overdue Loans</h5>
                <p class="text-muted">All loans are up to date!</p>
            </div>
        @endif
    </div>
</div>

<!-- Contact Modal -->
@foreach($overdueLoans as $loan)
<div class="modal fade" id="contactModal{{ $loan->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contact Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('collections.contact', $loan) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <p class="form-control-plaintext">{{ $loan->client->full_name ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label for="contact_method{{ $loan->id }}" class="form-label">Contact Method</label>
                        <select class="form-select" id="contact_method{{ $loan->id }}" name="contact_method" required>
                            <option value="">Select Method</option>
                            <option value="phone">Phone Call</option>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                            <option value="visit">Visit</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="message{{ $loan->id }}" class="form-label">Message</label>
                        <textarea class="form-control" id="message{{ $loan->id }}" name="message" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="follow_up_date{{ $loan->id }}" class="form-label">Follow-up Date</label>
                        <input type="date" class="form-control" id="follow_up_date{{ $loan->id }}" name="follow_up_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Contact</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Escalate Modal -->
<div class="modal fade" id="escalateModal{{ $loan->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Escalate Loan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('collections.escalate', $loan) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <p class="form-control-plaintext">{{ $loan->client->full_name ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label for="escalation_level{{ $loan->id }}" class="form-label">Escalation Level</label>
                        <select class="form-select" id="escalation_level{{ $loan->id }}" name="escalation_level" required>
                            <option value="">Select Level</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="manager">Manager</option>
                            <option value="legal">Legal Action</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="escalation_reason{{ $loan->id }}" class="form-label">Reason for Escalation</label>
                        <textarea class="form-control" id="escalation_reason{{ $loan->id }}" name="escalation_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Escalate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
