@extends('layouts.app')

@section('title', 'Loans')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Loans</h1>
    <p class="page-subtitle">Manage loan applications and active loans.</p>
</div>

<!-- Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2">
                <a href="{{ route('loans.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Loan
                </a>
                <a href="{{ route('loan-applications.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-file-alt"></i> Applications
                </a>
            </div>
            <div class="d-flex gap-2">
                <select class="form-select" style="width: 150px;" onchange="filterByStatus(this.value)">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="defaulted" {{ request('status') == 'defaulted' ? 'selected' : '' }}>Defaulted</option>
                </select>
                <input type="text" class="form-control" placeholder="Search loans..." style="width: 300px;">
                <button class="btn btn-outline-secondary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loans Table -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Loans</h6>
    </div>
    <div class="card-body">
        @if($loans->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Loan #</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Interest Rate</th>
                            <th>Term</th>
                            <th>Outstanding</th>
                            <th>Status</th>
                            <th>Branch</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loans as $loan)
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
                                <strong>${{ number_format($loan->amount, 2) }}</strong>
                            </td>
                            <td>{{ $loan->interest_rate }}%</td>
                            <td>{{ $loan->term_months }} months</td>
                            <td>
                                <strong class="text-{{ $loan->outstanding_balance > 0 ? 'warning' : 'success' }}">
                                    ${{ number_format($loan->outstanding_balance, 2) }}
                                </strong>
                            </td>
                            <td>
                                <span class="badge bg-{{ 
                                    $loan->status === 'active' ? 'success' : 
                                    ($loan->status === 'pending' ? 'warning' : 
                                    ($loan->status === 'overdue' ? 'danger' : 
                                    ($loan->status === 'completed' ? 'info' : 'secondary'))) 
                                }}">
                                    {{ ucfirst($loan->status) }}
                                </span>
                            </td>
                            <td>{{ $loan->branch->name ?? 'N/A' }}</td>
                            <td>{{ $loan->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('loans.edit', $loan) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($loan->status === 'pending')
                                        <form action="{{ route('loans.approve', $loan) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($loan->status === 'approved')
                                        <form action="{{ route('loans.disburse', $loan) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-info" title="Disburse">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($loan->status === 'pending')
                                        <form action="{{ route('loans.destroy', $loan) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this loan?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
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
            <div class="d-flex justify-content-center mt-4">
                {{ $loans->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Loans Found</h5>
                <p class="text-muted">Start by creating a new loan.</p>
                <a href="{{ route('loans.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First Loan
                </a>
            </div>
        @endif
    </div>
</div>

<script>
function filterByStatus(status) {
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    window.location = url;
}
</script>
@endsection
