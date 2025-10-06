@extends('layouts.app')

@section('title', 'Create Transaction')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Create Transaction</h1>
    <p class="page-subtitle">Record a new financial transaction.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Transaction Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('transactions.store') }}" method="POST">
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
                                <label for="type" class="form-label">Transaction Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="deposit" {{ old('type') == 'deposit' ? 'selected' : '' }}>Deposit</option>
                                    <option value="withdrawal" {{ old('type') == 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                                    <option value="loan_disbursement" {{ old('type') == 'loan_disbursement' ? 'selected' : '' }}>Loan Disbursement</option>
                                    <option value="loan_repayment" {{ old('type') == 'loan_repayment' ? 'selected' : '' }}>Loan Repayment</option>
                                    <option value="transfer" {{ old('type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                    <option value="fee" {{ old('type') == 'fee' ? 'selected' : '' }}>Fee</option>
                                </select>
                                @error('type')
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
                                       id="amount" name="amount" value="{{ old('amount') }}" 
                                       step="0.01" min="0.01" required>
                                @error('amount')
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="loan_id" class="form-label">Related Loan</label>
                                <select class="form-select @error('loan_id') is-invalid @enderror" 
                                        id="loan_id" name="loan_id">
                                    <option value="">Select Loan (Optional)</option>
                                    @foreach($loans as $loan)
                                        <option value="{{ $loan->id }}" {{ old('loan_id') == $loan->id ? 'selected' : '' }}>
                                            {{ $loan->loan_number }} - ${{ number_format($loan->amount, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('loan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="savings_account_id" class="form-label">Savings Account</label>
                                <select class="form-select @error('savings_account_id') is-invalid @enderror" 
                                        id="savings_account_id" name="savings_account_id">
                                    <option value="">Select Account (Optional)</option>
                                    @foreach($savingsAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('savings_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_number }} - ${{ number_format($account->balance, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('savings_account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Transaction
                        </button>
                        <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
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
                <h6 class="m-0 font-weight-bold text-primary">Transaction Types</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Deposit</strong>
                    <p class="small text-muted mb-0">Money coming into the system (positive amount).</p>
                </div>
                <div class="mb-3">
                    <strong>Withdrawal</strong>
                    <p class="small text-muted mb-0">Money going out of the system (negative amount).</p>
                </div>
                <div class="mb-3">
                    <strong>Loan Disbursement</strong>
                    <p class="small text-muted mb-0">Money lent to a client (negative amount).</p>
                </div>
                <div class="mb-3">
                    <strong>Loan Repayment</strong>
                    <p class="small text-muted mb-0">Money received from loan repayment (positive amount).</p>
                </div>
                <div class="mb-3">
                    <strong>Transfer</strong>
                    <p class="small text-muted mb-0">Money transferred between accounts.</p>
                </div>
                <div class="mb-3">
                    <strong>Fee</strong>
                    <p class="small text-muted mb-0">Service fees or charges (negative amount).</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
