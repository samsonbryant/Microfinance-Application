@extends('layouts.app')

@section('title', 'Fixed Deposits')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Fixed Deposits</h1>
    <p class="page-subtitle">Manage fixed deposit accounts and their maturity schedules.</p>
</div>

<!-- Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2">
                <a href="{{ route('savings-accounts.create') }}?type=fixed_deposit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Fixed Deposit
                </a>
            </div>
            <div class="d-flex gap-2">
                <select class="form-select" style="width: 150px;">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="matured">Matured</option>
                    <option value="closed">Closed</option>
                </select>
                <input type="text" class="form-control" placeholder="Search fixed deposits..." style="width: 300px;">
                <button class="btn btn-outline-secondary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Fixed Deposits Table -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Fixed Deposits</h6>
    </div>
    <div class="card-body">
        @if($fixedDeposits->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Account #</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Interest Rate</th>
                            <th>Term</th>
                            <th>Maturity Date</th>
                            <th>Status</th>
                            <th>Branch</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fixedDeposits as $deposit)
                        <tr>
                            <td>
                                <strong>{{ $deposit->account_number }}</strong>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $deposit->client->full_name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $deposit->client->client_number ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <strong class="text-success">${{ number_format($deposit->balance, 2) }}</strong>
                            </td>
                            <td>{{ $deposit->interest_rate }}%</td>
                            <td>
                                @if($deposit->maturity_date)
                                    {{ $deposit->created_at->diffInMonths($deposit->maturity_date) }} months
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($deposit->maturity_date)
                                    <span class="text-{{ $deposit->maturity_date->isFuture() ? 'info' : 'warning' }}">
                                        {{ $deposit->maturity_date->format('M d, Y') }}
                                    </span>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ 
                                    $deposit->status === 'active' ? 'success' : 
                                    ($deposit->status === 'matured' ? 'warning' : 'danger') 
                                }}">
                                    {{ ucfirst($deposit->status) }}
                                </span>
                            </td>
                            <td>{{ $deposit->branch->name ?? 'N/A' }}</td>
                            <td>{{ $deposit->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('savings-accounts.show', $deposit) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('savings-accounts.edit', $deposit) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($deposit->status === 'matured')
                                        <button class="btn btn-sm btn-outline-success" title="Renew">
                                            <i class="fas fa-redo"></i>
                                        </button>
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
                {{ $fixedDeposits->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-coins fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Fixed Deposits Found</h5>
                <p class="text-muted">Start by creating a new fixed deposit account.</p>
                <a href="{{ route('savings-accounts.create') }}?type=fixed_deposit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First Fixed Deposit
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
