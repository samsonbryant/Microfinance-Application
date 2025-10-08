@extends('layouts.app')

@section('title', 'Record Loan Repayment')

@section('content')
<div class="page-header">
    <h1 class="page-title">Record Loan Repayment</h1>
    <p class="page-subtitle">Process a loan repayment transaction</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Repayment Details</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('loan-repayments.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="loan_id" class="form-label">Select Loan <span class="text-danger">*</span></label>
                        <select class="form-select @error('loan_id') is-invalid @enderror" id="loan_id" name="loan_id" required>
                            <option value="">Select Active Loan</option>
                            @foreach($loans as $loan)
                                <option value="{{ $loan->id }}" {{ old('loan_id') == $loan->id ? 'selected' : '' }}>
                                    {{ $loan->loan_number }} - {{ $loan->client->full_name }} 
                                    (Balance: ${{ number_format($loan->outstanding_balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                        @error('loan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
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
                                <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                                    <option value="">Select Method</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>Check</option>
                                    <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reference_number" class="form-label">Reference Number</label>
                        <input type="text" class="form-control @error('reference_number') is-invalid @enderror" 
                               id="reference_number" name="reference_number" value="{{ old('reference_number') }}">
                        @error('reference_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('loan-repayments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Record Repayment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Info Card -->
    <div class="col-lg-4">
        <div class="card shadow bg-info text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Payment Info</h5>
                <p class="mb-2"><strong>Processing Time:</strong> Real-time</p>
                <p class="mb-2"><strong>Auto Updates:</strong> Loan balance will be updated automatically</p>
                <p class="mb-0"><strong>Completion:</strong> If balance reaches zero, loan will be marked as completed</p>
            </div>
        </div>
    </div>
</div>
@endsection

