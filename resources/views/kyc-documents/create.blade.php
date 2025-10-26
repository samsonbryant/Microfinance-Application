@extends('layouts.app')

@section('title', 'Upload KYC Document')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-upload text-primary me-2"></i>Upload KYC Document
            </h1>
            <p class="text-muted mb-0">Upload client identity and compliance documents</p>
        </div>
        <a href="{{ route('kyc-documents.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to List
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

    <form action="{{ route('kyc-documents.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">Document Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                                <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                                    <option value="">Select Client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
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
                                    <option value="national_id" {{ old('document_type') === 'national_id' ? 'selected' : '' }}>National ID</option>
                                    <option value="passport" {{ old('document_type') === 'passport' ? 'selected' : '' }}>Passport</option>
                                    <option value="driving_license" {{ old('document_type') === 'driving_license' ? 'selected' : '' }}>Driving License</option>
                                    <option value="birth_certificate" {{ old('document_type') === 'birth_certificate' ? 'selected' : '' }}>Birth Certificate</option>
                                    <option value="utility_bill" {{ old('document_type') === 'utility_bill' ? 'selected' : '' }}>Utility Bill</option>
                                    <option value="bank_statement" {{ old('document_type') === 'bank_statement' ? 'selected' : '' }}>Bank Statement</option>
                                    <option value="salary_slip" {{ old('document_type') === 'salary_slip' ? 'selected' : '' }}>Salary Slip</option>
                                    <option value="business_license" {{ old('document_type') === 'business_license' ? 'selected' : '' }}>Business License</option>
                                    <option value="tax_certificate" {{ old('document_type') === 'tax_certificate' ? 'selected' : '' }}>Tax Certificate</option>
                                    <option value="other" {{ old('document_type') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('document_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="document_number" class="form-label">Document Number</label>
                                <input type="text" class="form-control @error('document_number') is-invalid @enderror" 
                                       id="document_number" name="document_number" value="{{ old('document_number') }}">
                                @error('document_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="issuing_authority" class="form-label">Issuing Authority</label>
                                <input type="text" class="form-control @error('issuing_authority') is-invalid @enderror" 
                                       id="issuing_authority" name="issuing_authority" value="{{ old('issuing_authority') }}">
                                @error('issuing_authority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="issue_date" class="form-label">Issue Date</label>
                                <input type="date" class="form-control @error('issue_date') is-invalid @enderror" 
                                       id="issue_date" name="issue_date" value="{{ old('issue_date') }}">
                                @error('issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                       id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                                @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="file" class="form-label">Upload Document <span class="text-danger">*</span></label>
                                <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                       id="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.gif">
                                <small class="text-muted">Accepted formats: PDF, JPG, PNG, GIF (max 10MB)</small>
                                @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="m-0 font-weight-bold">Upload Guidelines</h6>
                    </div>
                    <div class="card-body">
                        <h6 class="text-primary">Document Requirements:</h6>
                        <ul class="small">
                            <li>Documents must be clear and readable</li>
                            <li>All information must be visible</li>
                            <li>No expired documents (except for reference)</li>
                            <li>File size should not exceed 10MB</li>
                        </ul>

                        <h6 class="text-primary mt-3">Acceptable Formats:</h6>
                        <ul class="small">
                            <li>PDF documents</li>
                            <li>JPEG/JPG images</li>
                            <li>PNG images</li>
                            <li>GIF images</li>
                        </ul>

                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Note:</strong> Documents will be reviewed and verified by compliance team.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('kyc-documents.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i>Upload Document
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('#client_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select Client'
        });
    }
});
</script>
@endpush
@endsection

