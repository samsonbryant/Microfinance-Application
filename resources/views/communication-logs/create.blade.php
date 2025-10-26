@extends('layouts.app')

@section('title', 'Log Communication')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-comments text-primary me-2"></i>Log Communication
            </h1>
            <p class="text-muted mb-0">Record client communication and interaction</p>
        </div>
        <a href="{{ route('communication-logs.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <form action="{{ route('communication-logs.store') }}" method="POST">
        @csrf

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h6 class="m-0 font-weight-bold">Communication Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                        <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                            <option value="">Select Client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">
                                    {{ $client->full_name }} - {{ $client->client_number }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="communication_type" class="form-label">Communication Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('communication_type') is-invalid @enderror" id="communication_type" name="communication_type" required>
                            <option value="">Select Type</option>
                            <option value="call">Phone Call</option>
                            <option value="sms">SMS</option>
                            <option value="email">Email</option>
                            <option value="visit">Field Visit</option>
                            <option value="letter">Letter</option>
                        </select>
                        @error('communication_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                               id="subject" name="subject" value="{{ old('subject') }}" required>
                        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('message') is-invalid @enderror" 
                                  id="message" name="message" rows="4" required>{{ old('message') }}</textarea>
                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="response" class="form-label">Client Response</label>
                        <textarea class="form-control" id="response" name="response" rows="3">{{ old('response') }}</textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="outcome" class="form-label">Outcome/Notes</label>
                        <textarea class="form-control" id="outcome" name="outcome" rows="2">{{ old('outcome') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('communication-logs.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Communication Log
                    </button>
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

