@extends('layouts.app')

@section('title', 'Apply for Loan')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-plus me-2"></i>Apply for New Loan</h4>
                <a href="{{ route('borrower.loans.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Loans
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h6><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-alt me-2"></i>Loan Application Form
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('borrower.loans.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Loan Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                               id="amount" name="amount" value="{{ old('amount') }}" 
                                               min="1000" max="100000" step="100" required>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Minimum: $1,000 | Maximum: $100,000</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="term_months" class="form-label">Loan Term <span class="text-danger">*</span></label>
                                    <select class="form-select @error('term_months') is-invalid @enderror" 
                                            id="term_months" name="term_months" required>
                                        <option value="">Select Term</option>
                                        <option value="6" {{ old('term_months') == '6' ? 'selected' : '' }}>6 Months</option>
                                        <option value="12" {{ old('term_months') == '12' ? 'selected' : '' }}>12 Months</option>
                                        <option value="18" {{ old('term_months') == '18' ? 'selected' : '' }}>18 Months</option>
                                        <option value="24" {{ old('term_months') == '24' ? 'selected' : '' }}>24 Months</option>
                                        <option value="36" {{ old('term_months') == '36' ? 'selected' : '' }}>36 Months</option>
                                    </select>
                                    @error('term_months')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="loan_purpose" class="form-label">Loan Purpose <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('loan_purpose') is-invalid @enderror" 
                                      id="loan_purpose" name="loan_purpose" rows="4" 
                                      placeholder="Please describe the purpose of this loan..." required>{{ old('loan_purpose') }}</textarea>
                            @error('loan_purpose')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="monthly_income" class="form-label">Monthly Income</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control @error('monthly_income') is-invalid @enderror" 
                                               id="monthly_income" name="monthly_income" value="{{ old('monthly_income', $client->monthly_income ?? '') }}" 
                                               step="0.01">
                                    </div>
                                    @error('monthly_income')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="employment_status" class="form-label">Employment Status</label>
                                    <select class="form-select @error('employment_status') is-invalid @enderror" 
                                            id="employment_status" name="employment_status">
                                        <option value="">Select Status</option>
                                        <option value="employed" {{ old('employment_status') == 'employed' ? 'selected' : '' }}>Employed</option>
                                        <option value="self_employed" {{ old('employment_status') == 'self_employed' ? 'selected' : '' }}>Self-Employed</option>
                                        <option value="business_owner" {{ old('employment_status') == 'business_owner' ? 'selected' : '' }}>Business Owner</option>
                                        <option value="unemployed" {{ old('employment_status') == 'unemployed' ? 'selected' : '' }}>Unemployed</option>
                                    </select>
                                    @error('employment_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Application Process:</strong>
                                    <ol class="mb-0 mt-2">
                                        <li>You submit the application (Status: <span class="badge bg-warning">Pending</span>)</li>
                                        <li>Loan Officer reviews (Status: <span class="badge bg-info">Under Review</span>)</li>
                                        <li>Admin approves/rejects (Status: <span class="badge bg-success">Approved</span> or <span class="badge bg-danger">Rejected</span>)</li>
                                        <li>If approved, funds are disbursed (Status: <span class="badge bg-primary">Disbursed</span>)</li>
                                    </ol>
                                    <p class="mb-0 mt-2"><strong>Timeline:</strong> Decision within 2-3 business days. You'll receive real-time notifications!</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Application
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
                        <i class="fas fa-calculator me-2"></i>Loan Calculator
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Interest Rate</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="interest_rate" value="12" readonly>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Monthly Payment</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control" id="monthly_payment" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Total Interest</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control" id="total_interest" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Total Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control" id="total_amount" readonly>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mt-3">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-check-circle me-2"></i>Requirements
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Valid identification
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Proof of income
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Bank statements (3 months)
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Completed application
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    const termSelect = document.getElementById('term_months');
    const interestRateInput = document.getElementById('interest_rate');
    const monthlyPaymentInput = document.getElementById('monthly_payment');
    const totalInterestInput = document.getElementById('total_interest');
    const totalAmountInput = document.getElementById('total_amount');
    
    function calculateLoan() {
        const amount = parseFloat(amountInput.value) || 0;
        const term = parseInt(termSelect.value) || 0;
        const interestRate = parseFloat(interestRateInput.value) || 0;
        
        if (amount > 0 && term > 0) {
            const monthlyRate = interestRate / 100 / 12;
            const monthlyPayment = (amount * monthlyRate * Math.pow(1 + monthlyRate, term)) / 
                                 (Math.pow(1 + monthlyRate, term) - 1);
            const totalAmount = monthlyPayment * term;
            const totalInterest = totalAmount - amount;
            
            monthlyPaymentInput.value = monthlyPayment.toFixed(2);
            totalInterestInput.value = totalInterest.toFixed(2);
            totalAmountInput.value = totalAmount.toFixed(2);
        } else {
            monthlyPaymentInput.value = '0.00';
            totalInterestInput.value = '0.00';
            totalAmountInput.value = '0.00';
        }
    }
    
    amountInput.addEventListener('input', calculateLoan);
    termSelect.addEventListener('change', calculateLoan);
    
    // Initial calculation
    calculateLoan();
});
</script>
@endsection
