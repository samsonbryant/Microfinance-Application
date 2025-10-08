@extends('layouts.app')

@section('title', 'Create Chart of Account - Microbook-G5')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-plus me-2"></i>Create Chart of Account</h4>
                <a href="{{ route('accounting.chart-of-accounts') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Chart of Accounts
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-edit me-2"></i>Account Information
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('accounting.chart-of-accounts.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Account Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                           id="code" name="code" value="{{ old('code') }}" 
                                           placeholder="e.g., 5001" required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Unique code for the account (e.g., 5001, 5100)</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Account Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" 
                                            id="type" name="type" required onchange="updateNormalBalance()">
                                        <option value="">Select account type...</option>
                                        @foreach($accountTypes as $key => $label)
                                            <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" 
                                   placeholder="e.g., Office Supplies Expense" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Account Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" 
                                    id="category" name="category" required>
                                <option value="">Select category...</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Brief description of the account purpose">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="opening_balance" class="form-label">Opening Balance</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control @error('opening_balance') is-invalid @enderror" 
                                               id="opening_balance" name="opening_balance" 
                                               value="{{ old('opening_balance', 0) }}" 
                                               min="0" step="0.01">
                                    </div>
                                    @error('opening_balance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Initial balance for the account</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="parent_id" class="form-label">Parent Account</label>
                                    <select class="form-select @error('parent_id') is-invalid @enderror" 
                                            id="parent_id" name="parent_id">
                                        <option value="">No parent account</option>
                                        @foreach($parentAccounts as $account)
                                            <option value="{{ $account->id }}" {{ old('parent_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->code }} - {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('parent_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Optional: Link to a parent account</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Create Account
                                </button>
                                <a href="{{ route('accounting.chart-of-accounts') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle me-2"></i>Account Type Information
                    </h6>
                </div>
                <div class="card-body">
                    <div id="accountTypeInfo">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Select an account type to see detailed information about normal balance and usage.
                        </div>
                    </div>

                    <div class="mt-3">
                        <h6>Account Type Guidelines:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Assets:</strong> Resources owned by the company (Normal: Debit)</li>
                            <li><strong>Liabilities:</strong> Debts owed by the company (Normal: Credit)</li>
                            <li><strong>Equity:</strong> Owner's investment in the company (Normal: Credit)</li>
                            <li><strong>Revenue:</strong> Income earned by the company (Normal: Credit)</li>
                            <li><strong>Expenses:</strong> Costs incurred by the company (Normal: Debit)</li>
                        </ul>
                    </div>

                    <div class="mt-3">
                        <h6>Normal Balance Rules:</h6>
                        <div class="alert alert-warning">
                            <small>
                                <strong>Debit Accounts:</strong> Assets, Expenses<br>
                                <strong>Credit Accounts:</strong> Liabilities, Equity, Revenue
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function updateNormalBalance() {
    const type = document.getElementById('type').value;
    const infoDiv = document.getElementById('accountTypeInfo');
    
    let info = '';
    let normalBalance = '';
    
    switch(type) {
        case 'asset':
            info = '<div class="alert alert-primary"><strong>Assets:</strong> Resources owned by the company (Cash, Equipment, Loans to customers, etc.)</div>';
            normalBalance = '<div class="alert alert-danger"><strong>Normal Balance: DEBIT</strong><br>Increases with debit entries, decreases with credit entries</div>';
            break;
        case 'liability':
            info = '<div class="alert alert-warning"><strong>Liabilities:</strong> Debts owed by the company (Savings deposits, Accounts payable, etc.)</div>';
            normalBalance = '<div class="alert alert-success"><strong>Normal Balance: CREDIT</strong><br>Increases with credit entries, decreases with debit entries</div>';
            break;
        case 'equity':
            info = '<div class="alert alert-info"><strong>Owner\'s Equity:</strong> Owner\'s investment in the company (Capital, Retained earnings, etc.)</div>';
            normalBalance = '<div class="alert alert-success"><strong>Normal Balance: CREDIT</strong><br>Increases with credit entries, decreases with debit entries</div>';
            break;
        case 'revenue':
            info = '<div class="alert alert-success"><strong>Revenue:</strong> Income earned by the company (Interest income, Fees, etc.)</div>';
            normalBalance = '<div class="alert alert-success"><strong>Normal Balance: CREDIT</strong><br>Increases with credit entries, decreases with debit entries</div>';
            break;
        case 'expense':
            info = '<div class="alert alert-danger"><strong>Expenses:</strong> Costs incurred by the company (Salaries, Rent, Utilities, etc.)</div>';
            normalBalance = '<div class="alert alert-danger"><strong>Normal Balance: DEBIT</strong><br>Increases with debit entries, decreases with credit entries</div>';
            break;
        default:
            info = '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Select an account type to see detailed information.</div>';
            normalBalance = '';
    }
    
    infoDiv.innerHTML = info + normalBalance;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateNormalBalance();
});
</script>
@endsection
