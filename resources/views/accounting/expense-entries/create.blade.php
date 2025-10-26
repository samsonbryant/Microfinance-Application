@extends('layouts.app')

@section('title', 'Record Expense - Microbook-G5')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-plus me-2"></i>Record Expense Entry</h4>
                <a href="{{ route('accounting.expense-entries') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Expense Entries
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-receipt me-2"></i>Expense Information
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('accounting.expense-entries.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('expense_date') is-invalid @enderror" 
                                           id="expense_date" name="expense_date" 
                                           value="{{ old('expense_date', now()->toDateString()) }}" required>
                                    @error('expense_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="account_id" class="form-label">Expense Account <span class="text-danger">*</span></label>
                                    <select class="form-select @error('account_id') is-invalid @enderror" 
                                            id="account_id" name="account_id" required>
                                        <option value="">Select expense account...</option>
                                        @foreach($expenseAccounts as $account)
                                            <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->code }} - {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                               id="amount" name="amount" value="{{ old('amount') }}" 
                                               min="0.01" step="0.01" required>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="receipt_number" class="form-label">Receipt Number</label>
                                    <input type="text" class="form-control @error('receipt_number') is-invalid @enderror" 
                                           id="receipt_number" name="receipt_number" 
                                           value="{{ old('receipt_number') }}" 
                                           placeholder="e.g., RCP-2024-001">
                                    @error('receipt_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Describe the expense in detail..." required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="reference_number" class="form-label">Reference Number</label>
                            <input type="text" class="form-control @error('reference_number') is-invalid @enderror" 
                                   id="reference_number" name="reference_number" 
                                   value="{{ old('reference_number') }}" 
                                   placeholder="e.g., Invoice number, PO number, etc.">
                            @error('reference_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Optional: External reference number (invoice, purchase order, etc.)</div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note:</strong> This expense entry will be created in "Pending" status and will require approval before being posted to the general ledger.
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Record Expense
                                </button>
                                <a href="{{ route('accounting.expense-entries') }}" class="btn btn-secondary">
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
                        <i class="fas fa-info-circle me-2"></i>Expense Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <h6>Expense Entry Process:</h6>
                    <ol class="small">
                        <li><strong>Record:</strong> Create expense entry with details</li>
                        <li><strong>Pending:</strong> Entry waits for approval</li>
                        <li><strong>Approved:</strong> Manager approves the expense</li>
                        <li><strong>Posted:</strong> Entry is posted to general ledger</li>
                    </ol>

                    <h6 class="mt-3">Required Information:</h6>
                    <ul class="small">
                        <li>Expense date</li>
                        <li>Expense account (from chart of accounts)</li>
                        <li>Amount (must be greater than $0.01)</li>
                        <li>Detailed description</li>
                    </ul>

                    <h6 class="mt-3">Optional Information:</h6>
                    <ul class="small">
                        <li>Receipt number</li>
                        <li>Reference number (invoice, PO, etc.)</li>
                    </ul>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Ensure all information is accurate as changes may not be possible after approval.
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-chart-line me-2"></i>Recent Expenses
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <i class="fas fa-receipt fa-2x text-muted mb-2"></i>
                        <p class="text-muted small">Recent expense entries will appear here</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate receipt number if not provided
    const receiptInput = document.getElementById('receipt_number');
    const dateInput = document.getElementById('expense_date');
    
    if (!receiptInput.value && dateInput.value) {
        const date = new Date(dateInput.value);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        
        // Generate a simple receipt number
        const receiptNumber = `RCP-${year}${month}${day}-001`;
        receiptInput.value = receiptNumber;
    }
    
    // Update receipt number when date changes
    dateInput.addEventListener('change', function() {
        if (!receiptInput.value) {
            const date = new Date(this.value);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            
            const receiptNumber = `RCP-${year}${month}${day}-001`;
            receiptInput.value = receiptNumber;
        }
    });
});
</script>
@endsection