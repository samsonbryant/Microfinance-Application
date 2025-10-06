@extends('layouts.app')

@section('title', 'Edit Transaction')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Edit Transaction</h1>
    <p class="page-subtitle">Transaction: {{ $transaction->transaction_number }}</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Transaction Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('transactions.update', $transaction) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Transaction Number</label>
                                <p class="form-control-plaintext">{{ $transaction->transaction_number }}</p>
                                <small class="text-muted">Transaction number cannot be changed</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Client</label>
                                <p class="form-control-plaintext">
                                    {{ $transaction->client->full_name ?? 'N/A' }} ({{ $transaction->client->client_number ?? 'N/A' }})
                                </p>
                                <small class="text-muted">Client cannot be changed</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Transaction Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" name="type" required>
                                    <option value="deposit" {{ old('type', $transaction->type) == 'deposit' ? 'selected' : '' }}>Deposit</option>
                                    <option value="withdrawal" {{ old('type', $transaction->type) == 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                                    <option value="loan_disbursement" {{ old('type', $transaction->type) == 'loan_disbursement' ? 'selected' : '' }}>Loan Disbursement</option>
                                    <option value="loan_repayment" {{ old('type', $transaction->type) == 'loan_repayment' ? 'selected' : '' }}>Loan Repayment</option>
                                    <option value="transfer" {{ old('type', $transaction->type) == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                    <option value="fee" {{ old('type', $transaction->type) == 'fee' ? 'selected' : '' }}>Fee</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="pending" {{ old('status', $transaction->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ old('status', $transaction->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ old('status', $transaction->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="reversed" {{ old('status', $transaction->status) == 'reversed' ? 'selected' : '' }}>Reversed</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" name="amount" value="{{ old('amount', $transaction->amount) }}" 
                                       step="0.01" min="0.01" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Branch</label>
                                <p class="form-control-plaintext">{{ $transaction->branch->name ?? 'N/A' }}</p>
                                <small class="text-muted">Branch cannot be changed</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" required>{{ old('description', $transaction->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Balance After</label>
                                <p class="form-control-plaintext">${{ number_format($transaction->balance_after, 2) }}</p>
                                <small class="text-muted">Balance cannot be changed directly</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Created Date</label>
                                <p class="form-control-plaintext">{{ $transaction->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Transaction
                        </button>
                        <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transaction Summary -->
    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Transaction Summary</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h4 class="text-{{ $transaction->type === 'deposit' || $transaction->type === 'loan_repayment' ? 'success' : 'danger' }}">
                        {{ $transaction->type === 'deposit' || $transaction->type === 'loan_repayment' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                    </h4>
                    <p class="text-muted mb-0">{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</p>
                </div>
                
                <div class="mb-3">
                    <strong>Status:</strong><br>
                    <span class="badge bg-{{ 
                        $transaction->status === 'approved' ? 'success' : 
                        ($transaction->status === 'pending' ? 'warning' : 
                        ($transaction->status === 'rejected' ? 'danger' : 'secondary')) 
                    }}">
                        {{ ucfirst($transaction->status) }}
                    </span>
                </div>

                <div class="mb-3">
                    <strong>Client:</strong><br>
                    {{ $transaction->client->full_name ?? 'N/A' }}
                </div>

                <div class="mb-3">
                    <strong>Branch:</strong><br>
                    {{ $transaction->branch->name ?? 'N/A' }}
                </div>

                <div class="mb-3">
                    <strong>Created By:</strong><br>
                    {{ $transaction->createdBy->name ?? 'N/A' }}
                </div>

                <div class="mb-3">
                    <strong>Created:</strong><br>
                    {{ $transaction->created_at->format('M d, Y') }}
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow mt-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline-info">
                        <i class="fas fa-eye"></i> View Transaction
                    </a>
                    @if($transaction->status === 'pending')
                        <form action="{{ route('transactions.approve', $transaction) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        </form>
                    @endif
                    @if($transaction->status === 'approved')
                        <form action="{{ route('transactions.reverse', $transaction) }}" method="POST" onsubmit="return confirm('Are you sure you want to reverse this transaction?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-undo"></i> Reverse
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
