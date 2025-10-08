@extends('layouts.app')

@section('title', 'Edit Loan Application')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Loan Application</h1>
    <p class="page-subtitle">Update application #{{ $loanApplication->application_number }}</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Application Details</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('loan-applications.update', $loanApplication) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="requested_amount" class="form-label">Requested Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('requested_amount') is-invalid @enderror" 
                                       id="requested_amount" name="requested_amount" 
                                       value="{{ old('requested_amount', $loanApplication->requested_amount) }}" 
                                       step="0.01" min="1" required>
                                @error('requested_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="term_months" class="form-label">Term (Months) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('term_months') is-invalid @enderror" 
                                       id="term_months" name="term_months" 
                                       value="{{ old('term_months', $loanApplication->term_months) }}" min="1" required>
                                @error('term_months')
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
                                       id="interest_rate" name="interest_rate" 
                                       value="{{ old('interest_rate', $loanApplication->interest_rate) }}" 
                                       step="0.01" min="0" required>
                                @error('interest_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="pending" {{ old('status', $loanApplication->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ old('status', $loanApplication->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ old('status', $loanApplication->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="cancelled" {{ old('status', $loanApplication->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="purpose" class="form-label">Loan Purpose <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('purpose') is-invalid @enderror" 
                                  id="purpose" name="purpose" rows="3" required>{{ old('purpose', $loanApplication->purpose) }}</textarea>
                        @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Rejection Reason (if rejected)</label>
                        <textarea class="form-control @error('rejection_reason') is-invalid @enderror" 
                                  id="rejection_reason" name="rejection_reason" rows="3">{{ old('rejection_reason', $loanApplication->rejection_reason) }}</textarea>
                        @error('rejection_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('loan-applications.show', $loanApplication) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

