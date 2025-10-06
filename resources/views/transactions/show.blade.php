@extends('layouts.app')

@section('title', 'Transaction Details')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Transaction Details</h1>
    <p class="page-subtitle">Transaction: {{ $transaction->transaction_number }}</p>
</div>

<div class="row">
    <!-- Transaction Information -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Transaction Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Transaction Number</label>
                            <p class="form-control-plaintext">{{ $transaction->transaction_number }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Transaction Type</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ 
                                    $transaction->type === 'deposit' ? 'success' : 
                                    ($transaction->type === 'withdrawal' ? 'warning' : 
                                    ($transaction->type === 'loan_disbursement' ? 'info' : 
                                    ($transaction->type === 'loan_repayment' ? 'primary' : 'secondary'))) 
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
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
                                <strong>{{ $transaction->client->full_name ?? 'N/A' }}</strong><br>
                                <small class="text-muted">{{ $transaction->client->client_number ?? 'N/A' }}</small>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Amount</label>
                            <p class="form-control-plaintext">
                                <strong class="text-{{ $transaction->type === 'deposit' || $transaction->type === 'loan_repayment' ? 'success' : 'danger' }}">
                                    {{ $transaction->type === 'deposit' || $transaction->type === 'loan_repayment' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                                </strong>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ 
                                    $transaction->status === 'approved' ? 'success' : 
                                    ($transaction->status === 'pending' ? 'warning' : 
                                    ($transaction->status === 'rejected' ? 'danger' : 'secondary')) 
                                }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Branch</label>
                            <p class="form-control-plaintext">{{ $transaction->branch->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <p class="form-control-plaintext">{{ $transaction->description }}</p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Balance After</label>
                            <p class="form-control-plaintext">${{ number_format($transaction->balance_after, 2) }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Created By</label>
                            <p class="form-control-plaintext">{{ $transaction->createdBy->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Created Date</label>
                            <p class="form-control-plaintext">{{ $transaction->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Updated Date</label>
                            <p class="form-control-plaintext">{{ $transaction->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Transaction
                    </a>
                    @if($transaction->status === 'pending')
                        <form action="{{ route('transactions.approve', $transaction) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        </form>
                    @endif
                    @if($transaction->status === 'approved')
                        <form action="{{ route('transactions.reverse', $transaction) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to reverse this transaction?')">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-undo"></i> Reverse
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Information -->
    <div class="col-lg-4">
        <!-- Related Loan -->
        @if($transaction->loan)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Related Loan</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Loan Number:</strong><br>
                    <a href="{{ route('loans.show', $transaction->loan) }}">{{ $transaction->loan->loan_number }}</a>
                </div>
                <div class="mb-2">
                    <strong>Amount:</strong><br>
                    ${{ number_format($transaction->loan->amount, 2) }}
                </div>
                <div class="mb-2">
                    <strong>Status:</strong><br>
                    <span class="badge bg-{{ $transaction->loan->status === 'active' ? 'success' : 'warning' }}">
                        {{ ucfirst($transaction->loan->status) }}
                    </span>
                </div>
            </div>
        </div>
        @endif

        <!-- Related Savings Account -->
        @if($transaction->savingsAccount)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Related Savings Account</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Account Number:</strong><br>
                    <a href="{{ route('savings-accounts.show', $transaction->savingsAccount) }}">{{ $transaction->savingsAccount->account_number }}</a>
                </div>
                <div class="mb-2">
                    <strong>Balance:</strong><br>
                    ${{ number_format($transaction->savingsAccount->balance, 2) }}
                </div>
                <div class="mb-2">
                    <strong>Type:</strong><br>
                    {{ ucfirst(str_replace('_', ' ', $transaction->savingsAccount->account_type)) }}
                </div>
            </div>
        </div>
        @endif

        <!-- Transaction Actions -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-outline-warning">
                        <i class="fas fa-edit"></i> Edit Transaction
                    </a>
                    @if($transaction->status === 'pending')
                        <form action="{{ route('transactions.approve', $transaction) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="fas fa-check"></i> Approve Transaction
                            </button>
                        </form>
                    @endif
                    @if($transaction->status === 'approved')
                        <form action="{{ route('transactions.reverse', $transaction) }}" method="POST" onsubmit="return confirm('Are you sure you want to reverse this transaction?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-undo"></i> Reverse Transaction
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
