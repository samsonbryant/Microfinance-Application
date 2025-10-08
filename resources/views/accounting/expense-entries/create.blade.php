@extends('layouts.app')

@section('title', 'Record Expense Entry - Microbook-G5')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-plus me-2"></i>Record Expense Entry</h4>
                <a href="{{ route('accounting.expense-entries.index') }}" class="btn btn-secondary">
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

                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" name="amount" value="{{ old('amount') }}" 
                                       min="0.01" step="0.01" required placeholder="0.00">
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reference_number" class="form-label">Reference Number</label>
                                    <input type="text" class="form-control @error('reference_number') is-invalid @enderror" 
                                           id="reference_number" name="reference_number" 
                                           value="{{ old('reference_number') }}" 
                                           placeholder="Invoice number, receipt number, etc.">
                                    @error('reference_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Optional: Reference number from vendor/supplier</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="receipt_number" class="form-label">Receipt Number</label>
                                    <input type="text" class="form-control @error('receipt_number') is-invalid @enderror" 
                                           id="receipt_number" name="receipt_number" 
                                           value="{{ old('receipt_number') }}" 
                                           placeholder="Receipt or voucher number">
                                    @error('receipt_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Optional: Internal receipt/voucher number</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Record Expense
                                </button>
                                <a href="{{ route('accounting.expense-entries.index') }}" class="btn btn-secondary">
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
                        <i class="fas fa-info-circle me-2"></i>Expense Entry Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Important:</strong> All expense entries require approval before they can be posted to the general ledger.
                    </div>

                    <h6>Expense Categories:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Salaries & Wages</li>
                        <li><i class="fas fa-check text-success me-2"></i>Rent Expense</li>
                        <li><i class="fas fa-check text-success me-2"></i>Communication & Internet</li>
                        <li><i class="fas fa-check text-success me-2"></i>Legal Fees</li>
                        <li><i class="fas fa-check text-success me-2"></i>Subscription Fees</li>
                        <li><i class="fas fa-check text-success me-2"></i>Utilities</li>
                        <li><i class="fas fa-check text-success me-2"></i>Other Expenses</li>
                    </ul>

                    <div class="mt-3">
                        <h6>Required Information:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-asterisk text-danger me-2"></i>Expense Date</li>
                            <li><i class="fas fa-asterisk text-danger me-2"></i>Expense Account</li>
                            <li><i class="fas fa-asterisk text-danger me-2"></i>Amount</li>
                            <li><i class="fas fa-asterisk text-danger me-2"></i>Description</li>
                        </ul>
                    </div>

                    <div class="mt-3">
                        <h6>Workflow:</h6>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-warning me-2">1</span>
                            <small>Record Expense</small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-info me-2">2</span>
                            <small>Manager Approval</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success me-2">3</span>
                            <small>Post to General Ledger</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>Accounting Impact
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">When this expense is posted, the following accounting entries will be made:</p>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Account</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Selected Expense Account</td>
                                    <td class="text-danger">$XXX.XX</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>Cash on Hand</td>
                                    <td>-</td>
                                    <td class="text-success">$XXX.XX</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        This follows double-entry bookkeeping principles.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-format amount input
    const amountInput = document.getElementById('amount');
    amountInput.addEventListener('blur', function() {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });

    // Auto-generate receipt number if not provided
    const receiptNumberInput = document.getElementById('receipt_number');
    const referenceNumberInput = document.getElementById('reference_number');
    
    if (!receiptNumberInput.value && !referenceNumberInput.value) {
        const today = new Date();
        const receiptNumber = 'RCP' + today.getFullYear() + (today.getMonth() + 1).toString().padStart(2, '0') + today.getDate().toString().padStart(2, '0') + Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        receiptNumberInput.placeholder = receiptNumber;
    }
});
</script>
@endsection
