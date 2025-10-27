@extends('layouts.app')

@section('title', 'Create Reconciliation')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-balance-scale text-primary me-2"></i>Create Reconciliation
            </h1>
            <p class="text-muted mb-0">Reconcile account balances with bank statements</p>
        </div>
        <a href="{{ route('accounting.reconciliations') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Reconciliations
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Reconciliation Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('accounting.reconciliations.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Reconciliation Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    @foreach($reconciliationTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="account_id" class="form-label">Account <span class="text-danger">*</span></label>
                                <select name="account_id" id="account_id" class="form-select @error('account_id') is-invalid @enderror" required>
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                    @endforeach
                                </select>
                                @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="reconciliation_date" class="form-label">Reconciliation Date <span class="text-danger">*</span></label>
                                <input type="date" name="reconciliation_date" id="reconciliation_date" 
                                       class="form-control @error('reconciliation_date') is-invalid @enderror" 
                                       value="{{ old('reconciliation_date', now()->toDateString()) }}" required>
                                @error('reconciliation_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="statement_balance" class="form-label">Statement Balance <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" name="statement_balance" id="statement_balance" 
                                           class="form-control @error('statement_balance') is-invalid @enderror" required>
                                </div>
                                @error('statement_balance') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" 
                                          rows="3" placeholder="Add any notes about this reconciliation...">{{ old('notes') }}</textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Reconciliation
                            </button>
                            <a href="{{ route('accounting.reconciliations') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle"></i> Reconciliation Guide
                    </h6>
                </div>
                <div class="card-body">
                    <h6>Steps:</h6>
                    <ol class="mb-0">
                        <li class="mb-2">Select reconciliation type</li>
                        <li class="mb-2">Choose the account to reconcile</li>
                        <li class="mb-2">Enter the statement balance</li>
                        <li class="mb-2">System will compare with book balance</li>
                        <li class="mb-2">Identify and mark discrepancies</li>
                        <li>Complete the reconciliation</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

