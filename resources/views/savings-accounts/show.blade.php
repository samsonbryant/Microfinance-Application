@extends('layouts.app')

@section('title', 'Savings Account Details')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Savings Account Details</h1>
    <p class="page-subtitle">Account: {{ $savingsAccount->account_number }}</p>
</div>

<div class="row">
    <!-- Account Information -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Account Number</label>
                            <p class="form-control-plaintext">{{ $savingsAccount->account_number }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Account Type</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ $savingsAccount->account_type === 'regular' ? 'primary' : ($savingsAccount->account_type === 'fixed_deposit' ? 'success' : 'warning') }}">
                                    {{ ucfirst(str_replace('_', ' ', $savingsAccount->account_type)) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Client</label>
                            <p class="form-control-plaintext">
                                <strong>{{ $savingsAccount->client->full_name ?? 'N/A' }}</strong><br>
                                <small class="text-muted">{{ $savingsAccount->client->client_number ?? 'N/A' }}</small>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Branch</label>
                            <p class="form-control-plaintext">{{ $savingsAccount->branch->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Current Balance</label>
                            <p class="form-control-plaintext">
                                <strong class="text-success">${{ number_format($savingsAccount->balance, 2) }}</strong>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Interest Rate</label>
                            <p class="form-control-plaintext">{{ $savingsAccount->interest_rate }}%</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Minimum Balance</label>
                            <p class="form-control-plaintext">${{ number_format($savingsAccount->minimum_balance, 2) }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ $savingsAccount->status === 'active' ? 'success' : ($savingsAccount->status === 'suspended' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($savingsAccount->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Created By</label>
                            <p class="form-control-plaintext">{{ $savingsAccount->createdBy->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Created Date</label>
                            <p class="form-control-plaintext">{{ $savingsAccount->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('savings-accounts.edit', $savingsAccount) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Account
                    </a>
                    <a href="{{ route('savings-accounts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6>
            </div>
            <div class="card-body">
                @if($savingsAccount->transactions && $savingsAccount->transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($savingsAccount->transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->type === 'deposit' ? 'success' : 'warning' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td>${{ number_format($transaction->amount, 2) }}</td>
                                    <td>{{ $transaction->description }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->status === 'approved' ? 'success' : 'warning' }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-exchange-alt fa-2x text-muted mb-3"></i>
                        <p class="text-muted">No transactions found for this account.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4">
        <!-- Deposit/Withdrawal -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#depositModal">
                        <i class="fas fa-plus"></i> Make Deposit
                    </button>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#withdrawalModal">
                        <i class="fas fa-minus"></i> Make Withdrawal
                    </button>
                </div>
            </div>
        </div>

        <!-- Account Statistics -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Account Statistics</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h4 class="text-primary">${{ number_format($savingsAccount->balance, 2) }}</h4>
                    <p class="text-muted mb-0">Current Balance</p>
                </div>
                
                <div class="row text-center">
                    <div class="col-6">
                        <h6 class="text-success">{{ $savingsAccount->transactions->where('type', 'deposit')->count() }}</h6>
                        <small class="text-muted">Deposits</small>
                    </div>
                    <div class="col-6">
                        <h6 class="text-warning">{{ $savingsAccount->transactions->where('type', 'withdrawal')->count() }}</h6>
                        <small class="text-muted">Withdrawals</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deposit Modal -->
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Make Deposit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('savings-accounts.deposit', $savingsAccount) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Process Deposit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Withdrawal Modal -->
<div class="modal fade" id="withdrawalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Make Withdrawal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('savings-accounts.withdraw', $savingsAccount) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" max="{{ $savingsAccount->balance }}" required>
                        <small class="text-muted">Available balance: ${{ number_format($savingsAccount->balance, 2) }}</small>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Process Withdrawal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
