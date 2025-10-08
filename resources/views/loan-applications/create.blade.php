@extends('layouts.app')

@section('title', 'New Loan Application')

@section('content')
<div class="page-header">
    <h1 class="page-title">New Loan Application</h1>
    <p class="page-subtitle">Submit a new loan application</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Application Details</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('loan-applications.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                                <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
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
                                <select class="form-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id" required>
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
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
                                <label for="loan_type" class="form-label">Loan Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('loan_type') is-invalid @enderror" id="loan_type" name="loan_type" required>
                                    <option value="">Select Type</option>
                                    <option value="personal" {{ old('loan_type') == 'personal' ? 'selected' : '' }}>Personal Loan</option>
                                    <option value="business" {{ old('loan_type') == 'business' ? 'selected' : '' }}>Business Loan</option>
                                    <option value="emergency" {{ old('loan_type') == 'emergency' ? 'selected' : '' }}>Emergency Loan</option>
                                    <option value="agriculture" {{ old('loan_type') == 'agriculture' ? 'selected' : '' }}>Agriculture Loan</option>
                                </select>
                                @error('loan_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="requested_amount" class="form-label">Requested Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('requested_amount') is-invalid @enderror" 
                                       id="requested_amount" name="requested_amount" value="{{ old('requested_amount') }}" 
                                       step="0.01" min="1" required>
                                @error('requested_amount')
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
                                       id="term_months" name="term_months" value="{{ old('term_months') }}" min="1" required>
                                @error('term_months')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="interest_rate" class="form-label">Interest Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('interest_rate') is-invalid @enderror" 
                                       id="interest_rate" name="interest_rate" value="{{ old('interest_rate', 15) }}" 
                                       step="0.01" min="0" required>
                                @error('interest_rate')
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="employment_status" class="form-label">Employment Status</label>
                                <select class="form-select @error('employment_status') is-invalid @enderror" id="employment_status" name="employment_status">
                                    <option value="">Select Status</option>
                                    <option value="employed" {{ old('employment_status') == 'employed' ? 'selected' : '' }}>Employed</option>
                                    <option value="self_employed" {{ old('employment_status') == 'self_employed' ? 'selected' : '' }}>Self Employed</option>
                                    <option value="unemployed" {{ old('employment_status') == 'unemployed' ? 'selected' : '' }}>Unemployed</option>
                                </select>
                                @error('employment_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="monthly_income" class="form-label">Monthly Income</label>
                                <input type="number" class="form-control @error('monthly_income') is-invalid @enderror" 
                                       id="monthly_income" name="monthly_income" value="{{ old('monthly_income') }}" 
                                       step="0.01" min="0">
                                @error('monthly_income')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="collateral_type" class="form-label">Collateral Type</label>
                                <input type="text" class="form-control @error('collateral_type') is-invalid @enderror" 
                                       id="collateral_type" name="collateral_type" value="{{ old('collateral_type') }}">
                                @error('collateral_type')
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

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('loan-applications.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Submit Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

