@extends('layouts.app')

@section('title', 'Edit Savings Account')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Edit Savings Account</h1>
    <p class="page-subtitle">Account: {{ $savingsAccount->account_number }}</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('savings-accounts.update', $savingsAccount) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Account Number</label>
                                <p class="form-control-plaintext">{{ $savingsAccount->account_number }}</p>
                                <small class="text-muted">Account number cannot be changed</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Client</label>
                                <p class="form-control-plaintext">
                                    {{ $savingsAccount->client->full_name ?? 'N/A' }} ({{ $savingsAccount->client->client_number ?? 'N/A' }})
                                </p>
                                <small class="text-muted">Client cannot be changed</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_type" class="form-label">Account Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('account_type') is-invalid @enderror" 
                                        id="account_type" name="account_type" required>
                                    <option value="regular" {{ old('account_type', $savingsAccount->account_type) == 'regular' ? 'selected' : '' }}>Regular Savings</option>
                                    <option value="fixed_deposit" {{ old('account_type', $savingsAccount->account_type) == 'fixed_deposit' ? 'selected' : '' }}>Fixed Deposit</option>
                                    <option value="emergency" {{ old('account_type', $savingsAccount->account_type) == 'emergency' ? 'selected' : '' }}>Emergency Fund</option>
                                </select>
                                @error('account_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="active" {{ old('status', $savingsAccount->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $savingsAccount->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="suspended" {{ old('status', $savingsAccount->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="closed" {{ old('status', $savingsAccount->status) == 'closed' ? 'selected' : '' }}>Closed</option>
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
                                <label for="interest_rate" class="form-label">Interest Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('interest_rate') is-invalid @enderror" 
                                       id="interest_rate" name="interest_rate" value="{{ old('interest_rate', $savingsAccount->interest_rate) }}" 
                                       step="0.01" min="0" max="100" required>
                                @error('interest_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="minimum_balance" class="form-label">Minimum Balance <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('minimum_balance') is-invalid @enderror" 
                                       id="minimum_balance" name="minimum_balance" value="{{ old('minimum_balance', $savingsAccount->minimum_balance) }}" 
                                       step="0.01" min="0" required>
                                @error('minimum_balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                <small class="text-muted">Balance cannot be changed directly</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Branch</label>
                                <p class="form-control-plaintext">{{ $savingsAccount->branch->name ?? 'N/A' }}</p>
                                <small class="text-muted">Branch cannot be changed</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Account
                        </button>
                        <a href="{{ route('savings-accounts.show', $savingsAccount) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Account Summary -->
    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Account Summary</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h4 class="text-primary">${{ number_format($savingsAccount->balance, 2) }}</h4>
                    <p class="text-muted mb-0">Current Balance</p>
                </div>
                
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <h6 class="text-success">{{ $savingsAccount->transactions->where('type', 'deposit')->count() }}</h6>
                        <small class="text-muted">Deposits</small>
                    </div>
                    <div class="col-6">
                        <h6 class="text-warning">{{ $savingsAccount->transactions->where('type', 'withdrawal')->count() }}</h6>
                        <small class="text-muted">Withdrawals</small>
                    </div>
                </div>

                <hr>

                <div class="mb-2">
                    <strong>Account Type:</strong><br>
                    <span class="badge bg-{{ $savingsAccount->account_type === 'regular' ? 'primary' : ($savingsAccount->account_type === 'fixed_deposit' ? 'success' : 'warning') }}">
                        {{ ucfirst(str_replace('_', ' ', $savingsAccount->account_type)) }}
                    </span>
                </div>

                <div class="mb-2">
                    <strong>Interest Rate:</strong><br>
                    {{ $savingsAccount->interest_rate }}%
                </div>

                <div class="mb-2">
                    <strong>Status:</strong><br>
                    <span class="badge bg-{{ $savingsAccount->status === 'active' ? 'success' : ($savingsAccount->status === 'suspended' ? 'warning' : 'danger') }}">
                        {{ ucfirst($savingsAccount->status) }}
                    </span>
                </div>

                <div class="mb-2">
                    <strong>Created:</strong><br>
                    {{ $savingsAccount->created_at->format('M d, Y') }}
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
                    <a href="{{ route('savings-accounts.show', $savingsAccount) }}" class="btn btn-outline-info">
                        <i class="fas fa-eye"></i> View Account
                    </a>
                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#depositModal">
                        <i class="fas fa-plus"></i> Make Deposit
                    </button>
                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#withdrawalModal">
                        <i class="fas fa-minus"></i> Make Withdrawal
                    </button>
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
