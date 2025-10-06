@extends('layouts.app')

@section('title', 'Create Savings Account')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Create Savings Account</h1>
    <p class="page-subtitle">Open a new savings account for a client.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('savings-accounts.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                                <select class="form-select @error('client_id') is-invalid @enderror" 
                                        id="client_id" name="client_id" required>
                                    <option value="">Select Client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->full_name }} ({{ $client->client_number }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('client_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_type" class="form-label">Account Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('account_type') is-invalid @enderror" 
                                        id="account_type" name="account_type" required>
                                    <option value="">Select Type</option>
                                    <option value="regular" {{ old('account_type') == 'regular' ? 'selected' : '' }}>Regular Savings</option>
                                    <option value="fixed_deposit" {{ old('account_type') == 'fixed_deposit' ? 'selected' : '' }}>Fixed Deposit</option>
                                    <option value="emergency" {{ old('account_type') == 'emergency' ? 'selected' : '' }}>Emergency Fund</option>
                                </select>
                                @error('account_type')
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
                                       id="interest_rate" name="interest_rate" value="{{ old('interest_rate', 5.0) }}" 
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
                                       id="minimum_balance" name="minimum_balance" value="{{ old('minimum_balance', 100) }}" 
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
                                <label for="initial_deposit" class="form-label">Initial Deposit</label>
                                <input type="number" class="form-control @error('initial_deposit') is-invalid @enderror" 
                                       id="initial_deposit" name="initial_deposit" value="{{ old('initial_deposit', 0) }}" 
                                       step="0.01" min="0">
                                @error('initial_deposit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                                <select class="form-select @error('branch_id') is-invalid @enderror" 
                                        id="branch_id" name="branch_id" required>
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id', auth()->user()->branch_id) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }} ({{ $branch->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Account
                        </button>
                        <a href="{{ route('savings-accounts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Help -->
    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Account Types</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Regular Savings</strong>
                    <p class="small text-muted mb-0">Standard savings account with flexible deposits and withdrawals.</p>
                </div>
                <div class="mb-3">
                    <strong>Fixed Deposit</strong>
                    <p class="small text-muted mb-0">Higher interest rate with fixed term and limited withdrawals.</p>
                </div>
                <div class="mb-3">
                    <strong>Emergency Fund</strong>
                    <p class="small text-muted mb-0">Quick access savings for emergency situations.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
