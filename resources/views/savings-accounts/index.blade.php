@extends('layouts.app')

@section('title', 'Savings Accounts')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Savings Accounts</h1>
    <p class="page-subtitle">Manage client savings accounts and deposits.</p>
</div>

<!-- Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2">
                <a href="{{ route('savings-accounts.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Savings Account
                </a>
            </div>
            <div class="d-flex gap-2">
                <input type="text" class="form-control" placeholder="Search accounts..." style="width: 300px;">
                <button class="btn btn-outline-secondary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Savings Accounts Table -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Savings Accounts</h6>
    </div>
    <div class="card-body">
        @if($savingsAccounts->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Account Number</th>
                            <th>Client</th>
                            <th>Account Type</th>
                            <th>Balance</th>
                            <th>Interest Rate</th>
                            <th>Status</th>
                            <th>Branch</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($savingsAccounts as $account)
                        <tr>
                            <td>
                                <strong>{{ $account->account_number }}</strong>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $account->client->full_name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $account->client->client_number ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $account->account_type === 'regular' ? 'primary' : ($account->account_type === 'fixed_deposit' ? 'success' : 'warning') }}">
                                    {{ ucfirst(str_replace('_', ' ', $account->account_type)) }}
                                </span>
                            </td>
                            <td>
                                <strong>${{ number_format($account->balance, 2) }}</strong>
                            </td>
                            <td>{{ $account->interest_rate }}%</td>
                            <td>
                                <span class="badge bg-{{ $account->status === 'active' ? 'success' : ($account->status === 'suspended' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($account->status) }}
                                </span>
                            </td>
                            <td>{{ $account->branch->name ?? 'N/A' }}</td>
                            <td>{{ $account->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('savings-accounts.show', $account) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('savings-accounts.edit', $account) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('savings-accounts.destroy', $account) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this account?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $savingsAccounts->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-piggy-bank fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Savings Accounts Found</h5>
                <p class="text-muted">Start by creating a new savings account for a client.</p>
                <a href="{{ route('savings-accounts.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First Account
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
