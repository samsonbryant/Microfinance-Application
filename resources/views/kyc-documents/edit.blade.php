@extends('layouts.app')

@section('title', 'Edit KYC Document')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit text-warning me-2"></i>Edit KYC Document
            </h1>
            <p class="text-muted mb-0">Update document information - #{{ $kycDocument->id }}</p>
        </div>
        <a href="{{ route('kyc-documents.show', $kycDocument) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Validation Errors</h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('kyc-documents.update', $kycDocument) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card shadow">
            <div class="card-header bg-warning">
                <h6 class="m-0 font-weight-bold">Document Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                        <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                            <option value="">Select Client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ (old('client_id', $kycDocument->client_id) == $client->id) ? 'selected' : '' }}>
                                    {{ $client->full_name }} - {{ $client->client_number }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="document_type" class="form-label">Document Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('document_type') is-invalid @enderror" id="document_type" name="document_type" required>
                            <option value="">Select Type</option>
                            <option value="national_id" {{ old('document_type', $kycDocument->document_type) === 'national_id' ? 'selected' : '' }}>National ID</option>
                            <option value="passport" {{ old('document_type', $kycDocument->document_type) === 'passport' ? 'selected' : '' }}>Passport</option>
                            <option value="driving_license" {{ old('document_type', $kycDocument->document_type) === 'driving_license' ? 'selected' : '' }}>Driving License</option>
                            <option value="birth_certificate" {{ old('document_type', $kycDocument->document_type) === 'birth_certificate' ? 'selected' : '' }}>Birth Certificate</option>
                            <option value="utility_bill" {{ old('document_type', $kycDocument->document_type) === 'utility_bill' ? 'selected' : '' }}>Utility Bill</option>
                            <option value="bank_statement" {{ old('document_type', $kycDocument->document_type) === 'bank_statement' ? 'selected' : '' }}>Bank Statement</option>
                            <option value="salary_slip" {{ old('document_type', $kycDocument->document_type) === 'salary_slip' ? 'selected' : '' }}>Salary Slip</option>
                            <option value="business_license" {{ old('document_type', $kycDocument->document_type) === 'business_license' ? 'selected' : '' }}>Business License</option>
                            <option value="tax_certificate" {{ old('document_type', $kycDocument->document_type) === 'tax_certificate' ? 'selected' : '' }}>Tax Certificate</option>
                            <option value="other" {{ old('document_type', $kycDocument->document_type) === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('document_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="document_number" class="form-label">Document Number</label>
                        <input type="text" class="form-control @error('document_number') is-invalid @enderror" 
                               id="document_number" name="document_number" value="{{ old('document_number', $kycDocument->document_number) }}">
                        @error('document_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="issuing_authority" class="form-label">Issuing Authority</label>
                        <input type="text" class="form-control @error('issuing_authority') is-invalid @enderror" 
                               id="issuing_authority" name="issuing_authority" value="{{ old('issuing_authority', $kycDocument->issuing_authority) }}">
                        @error('issuing_authority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="issue_date" class="form-label">Issue Date</label>
                        <input type="date" class="form-control @error('issue_date') is-invalid @enderror" 
                               id="issue_date" name="issue_date" value="{{ old('issue_date', $kycDocument->issue_date?->format('Y-m-d')) }}">
                        @error('issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                               id="expiry_date" name="expiry_date" value="{{ old('expiry_date', $kycDocument->expiry_date?->format('Y-m-d')) }}">
                        @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="file" class="form-label">Replace Document (Optional)</label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror" 
                               id="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.gif">
                        <small class="text-muted">Leave empty to keep current file. Max 10MB.</small>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3">{{ old('notes', $kycDocument->notes) }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('kyc-documents.show', $kycDocument) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i>Cancel
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save me-1"></i>Update Document
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

