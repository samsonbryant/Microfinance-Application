@extends('layouts.app')

@section('title', 'Add Collateral')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-shield-alt text-primary me-2"></i>Add New Collateral
            </h1>
            <p class="text-muted mb-0">Register new collateral for loan security</p>
        </div>
        <a href="{{ route('collaterals.index') }}" class="btn btn-secondary">
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

    <!-- Form Card -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Collateral Information</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('collaterals.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <!-- Client Selection -->
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
                        @error('client_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Collateral Type -->
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Collateral Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="property" {{ old('type') == 'property' ? 'selected' : '' }}>Property/Land</option>
                            <option value="vehicle" {{ old('type') == 'vehicle' ? 'selected' : '' }}>Vehicle</option>
                            <option value="equipment" {{ old('type') == 'equipment' ? 'selected' : '' }}>Equipment/Machinery</option>
                            <option value="jewelry" {{ old('type') == 'jewelry' ? 'selected' : '' }}>Jewelry</option>
                            <option value="electronics" {{ old('type') == 'electronics' ? 'selected' : '' }}>Electronics</option>
                            <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-md-12 mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" 
                                  name="description" rows="3" required>{{ old('description') }}</textarea>
                        <small class="text-muted">Provide detailed description of the collateral</small>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Estimated Value -->
                    <div class="col-md-6 mb-3">
                        <label for="value" class="form-label">Estimated Value <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control @error('value') is-invalid @enderror" 
                                   id="value" name="value" value="{{ old('value', 0) }}" 
                                   step="0.01" min="0" required>
                            <span class="input-group-text">USD</span>
                        </div>
                        @error('value')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Location -->
                    <div class="col-md-6 mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control @error('location') is-invalid @enderror" 
                               id="location" name="location" value="{{ old('location') }}" 
                               placeholder="e.g., Warehouse, Client's Home">
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Condition -->
                    <div class="col-md-6 mb-3">
                        <label for="condition" class="form-label">Condition</label>
                        <select class="form-select @error('condition') is-invalid @enderror" id="condition" name="condition">
                            <option value="">Select Condition</option>
                            <option value="excellent" {{ old('condition') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                            <option value="good" {{ old('condition') == 'good' ? 'selected' : '' }}>Good</option>
                            <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                            <option value="poor" {{ old('condition') == 'poor' ? 'selected' : '' }}>Poor</option>
                        </select>
                        @error('condition')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="col-md-12 mb-3">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" 
                                  name="notes" rows="2">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Document Upload -->
                    <div class="col-md-12 mb-3">
                        <label for="documents" class="form-label">Supporting Documents</label>
                        <input type="file" class="form-control @error('documents.*') is-invalid @enderror" 
                               id="documents" name="documents[]" multiple 
                               accept=".pdf,.jpg,.jpeg,.png,.gif">
                        <small class="text-muted">Upload photos, ownership documents, valuation reports (max 10MB each)</small>
                        @error('documents.*')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('collaterals.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Collateral
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for better dropdown UX
    if (typeof $.fn.select2 !== 'undefined') {
        $('#client_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select Client',
            allowClear: true
        });
    }
});
</script>
@endpush
@endsection

