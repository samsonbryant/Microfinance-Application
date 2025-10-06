@extends('layouts.app')

@section('title', 'Make Payment')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-money-bill-wave me-2"></i>Make Payment</h4>
                <a href="{{ route('borrower.loans.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Loans
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-credit-card me-2"></i>Payment Form
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('borrower.payments.store') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="loan_id" class="form-label">Select Loan <span class="text-danger">*</span></label>
                            <select class="form-select @error('loan_id') is-invalid @enderror" 
                                    id="loan_id" name="loan_id" required>
                                <option value="">Choose a loan...</option>
                                @foreach($loans as $loanOption)
                                    <option value="{{ $loanOption->id }}" 
                                            {{ ($loan && $loan->id == $loanOption->id) || old('loan_id') == $loanOption->id ? 'selected' : '' }}
                                            data-outstanding="{{ $loanOption->outstanding_balance }}"
                                            data-monthly="{{ $loanOption->monthly_payment }}">
                                        Loan #{{ $loanOption->id }} - Outstanding: ${{ number_format($loanOption->outstanding_balance, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('loan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" name="amount" value="{{ old('amount') }}" 
                                       min="0.01" step="0.01" required>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="payMonthly">
                                    Pay Monthly Amount
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success" id="payFull">
                                    Pay Full Balance
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select @error('payment_method') is-invalid @enderror" 
                                    id="payment_method" name="payment_method" required>
                                <option value="">Select payment method...</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="reference" class="form-label">Reference/Transaction ID</label>
                            <input type="text" class="form-control @error('reference') is-invalid @enderror" 
                                   id="reference" name="reference" value="{{ old('reference') }}" 
                                   placeholder="Enter transaction reference if applicable">
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Payment Processing:</strong> Your payment will be processed immediately and reflected in your account within 24 hours.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-2"></i>Process Payment
                                </button>
                                <a href="{{ route('borrower.loans.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle me-2"></i>Payment Information
                    </h6>
                </div>
                <div class="card-body">
                    <div id="loan-info" style="display: none;">
                        <div class="mb-3">
                            <strong>Outstanding Balance:</strong>
                            <div class="text-success" id="outstanding-balance">$0.00</div>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Monthly Payment:</strong>
                            <div class="text-primary" id="monthly-payment">$0.00</div>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Next Due Date:</strong>
                            <div id="next-due-date">N/A</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> Please ensure you have sufficient funds before processing the payment.
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
    const loanSelect = document.getElementById('loan_id');
    const amountInput = document.getElementById('amount');
    const payMonthlyBtn = document.getElementById('payMonthly');
    const payFullBtn = document.getElementById('payFull');
    const loanInfo = document.getElementById('loan-info');
    const outstandingBalance = document.getElementById('outstanding-balance');
    const monthlyPayment = document.getElementById('monthly-payment');
    
    function updateLoanInfo() {
        const selectedOption = loanSelect.options[loanSelect.selectedIndex];
        if (selectedOption.value) {
            const outstanding = selectedOption.dataset.outstanding;
            const monthly = selectedOption.dataset.monthly;
            
            outstandingBalance.textContent = '$' + parseFloat(outstanding).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            monthlyPayment.textContent = '$' + parseFloat(monthly).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            loanInfo.style.display = 'block';
        } else {
            loanInfo.style.display = 'none';
        }
    }
    
    loanSelect.addEventListener('change', updateLoanInfo);
    
    payMonthlyBtn.addEventListener('click', function() {
        const selectedOption = loanSelect.options[loanSelect.selectedIndex];
        if (selectedOption.value) {
            amountInput.value = selectedOption.dataset.monthly;
        }
    });
    
    payFullBtn.addEventListener('click', function() {
        const selectedOption = loanSelect.options[loanSelect.selectedIndex];
        if (selectedOption.value) {
            amountInput.value = selectedOption.dataset.outstanding;
        }
    });
    
    // Initial update
    updateLoanInfo();
});
</script>
@endsection
