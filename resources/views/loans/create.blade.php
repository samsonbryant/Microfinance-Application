@extends('layouts.app')

@section('title', 'Create Loan')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Create Loan</h1>
    <p class="page-subtitle">Process a new loan application.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Loan Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('loans.store') }}" method="POST">
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
                                <label for="amount" class="form-label">Loan Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" name="amount" value="{{ old('amount') }}" 
                                       step="0.01" min="100" max="1000000" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="interest_rate" class="form-label">Interest Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('interest_rate') is-invalid @enderror" 
                                       id="interest_rate" name="interest_rate" value="{{ old('interest_rate', 12) }}" 
                                       step="0.01" min="0" max="100" required>
                                @error('interest_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="term_months" class="form-label">Term (Months) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('term_months') is-invalid @enderror" 
                                       id="term_months" name="term_months" value="{{ old('term_months', 12) }}" 
                                       min="1" max="60" required>
                                @error('term_months')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="collateral_value" class="form-label">Collateral Value</label>
                                <input type="number" class="form-control @error('collateral_value') is-invalid @enderror" 
                                       id="collateral_value" name="collateral_value" value="{{ old('collateral_value') }}" 
                                       step="0.01" min="0">
                                @error('collateral_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="purpose" class="form-label">Loan Purpose <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('purpose') is-invalid @enderror" 
                                  id="purpose" name="purpose" rows="3" required>{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="collateral_description" class="form-label">Collateral Description</label>
                        <textarea class="form-control @error('collateral_description') is-invalid @enderror" 
                                  id="collateral_description" name="collateral_description" rows="3">{{ old('collateral_description') }}</textarea>
                        @error('collateral_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Loan
                        </button>
                        <a href="{{ route('loans.index') }}" class="btn btn-secondary">
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
                <h6 class="m-0 font-weight-bold text-primary">Loan Information</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted">
                    <strong>Loan Number:</strong> Will be automatically generated after creation.
                </p>
                <p class="small text-muted">
                    <strong>Status:</strong> Will be set to "Pending" and requires approval.
                </p>
                <p class="small text-muted">
                    <strong>Client Requirements:</strong> Only verified clients are eligible for loans.
                </p>
                <hr>
                <p class="small text-muted">
                    <i class="fas fa-info-circle"></i>
                    Fields marked with <span class="text-danger">*</span> are required.
                </p>
                <p class="small text-muted">
                    <i class="fas fa-shield-alt"></i>
                    All loan information will be encrypted and secured.
                </p>
            </div>
        </div>

        <!-- Loan Calculator -->
        <div class="card shadow mt-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Loan Calculator</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Monthly Payment</label>
                    <div id="monthly-payment" class="h5 text-primary">$0.00</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Total Interest</label>
                    <div id="total-interest" class="h6 text-info">$0.00</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Total Amount</label>
                    <div id="total-amount" class="h6 text-success">$0.00</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    const interestInput = document.getElementById('interest_rate');
    const termInput = document.getElementById('term_months');
    
    function calculateLoan() {
        const amount = parseFloat(amountInput.value) || 0;
        const interest = parseFloat(interestInput.value) || 0;
        const term = parseInt(termInput.value) || 0;
        
        if (amount > 0 && interest > 0 && term > 0) {
            const monthlyRate = interest / 100 / 12;
            const monthlyPayment = (amount * monthlyRate * Math.pow(1 + monthlyRate, term)) / 
                                 (Math.pow(1 + monthlyRate, term) - 1);
            const totalAmount = monthlyPayment * term;
            const totalInterest = totalAmount - amount;
            
            document.getElementById('monthly-payment').textContent = '$' + monthlyPayment.toFixed(2);
            document.getElementById('total-interest').textContent = '$' + totalInterest.toFixed(2);
            document.getElementById('total-amount').textContent = '$' + totalAmount.toFixed(2);
        } else {
            document.getElementById('monthly-payment').textContent = '$0.00';
            document.getElementById('total-interest').textContent = '$0.00';
            document.getElementById('total-amount').textContent = '$0.00';
        }
    }
    
    amountInput.addEventListener('input', calculateLoan);
    interestInput.addEventListener('input', calculateLoan);
    termInput.addEventListener('input', calculateLoan);
});
</script>
@endpush
@endsection
