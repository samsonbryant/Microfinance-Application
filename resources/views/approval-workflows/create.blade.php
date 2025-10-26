@extends('layouts.app')

@section('title', 'Create Approval Workflow')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-tasks text-primary me-2"></i>Create Approval Workflow
            </h1>
            <p class="text-muted mb-0">Set up multi-level approval process</p>
        </div>
        <a href="{{ route('approval-workflows.index') }}" class="btn btn-secondary">
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

    <form action="{{ route('approval-workflows.store') }}" method="POST">
        @csrf

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h6 class="m-0 font-weight-bold">Workflow Configuration</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="loan_id" class="form-label">Loan Application <span class="text-danger">*</span></label>
                        <select class="form-select @error('loan_id') is-invalid @enderror" id="loan_id" name="loan_id" required>
                            <option value="">Select Loan</option>
                            @foreach($loans as $loan)
                                <option value="{{ $loan->id }}" {{ old('loan_id') == $loan->id ? 'selected' : '' }}>
                                    {{ $loan->loan_number }} - {{ $loan->client->full_name }} - ${{ number_format($loan->amount, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('loan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="level" class="form-label">Approval Level <span class="text-danger">*</span></label>
                        <select class="form-select @error('level') is-invalid @enderror" id="level" name="level" required>
                            <option value="">Select Level</option>
                            <option value="1" {{ old('level') == 1 ? 'selected' : '' }}>Level 1 - Loan Officer Review</option>
                            <option value="2" {{ old('level') == 2 ? 'selected' : '' }}>Level 2 - Branch Manager Approval</option>
                            <option value="3" {{ old('level') == 3 ? 'selected' : '' }}>Level 3 - Senior Manager Approval</option>
                            <option value="4" {{ old('level') == 4 ? 'selected' : '' }}>Level 4 - Finance Director Approval</option>
                            <option value="5" {{ old('level') == 5 ? 'selected' : '' }}>Level 5 - CEO Final Approval</option>
                        </select>
                        @error('level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="approver_id" class="form-label">Assign To <span class="text-danger">*</span></label>
                        <select class="form-select @error('approver_id') is-invalid @enderror" id="approver_id" name="approver_id" required>
                            <option value="">Select Approver</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('approver_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} - {{ ucfirst($user->role) }}
                                </option>
                            @endforeach
                        </select>
                        @error('approver_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="comments" class="form-label">Comments/Instructions</label>
                        <textarea class="form-control @error('comments') is-invalid @enderror" 
                                  id="comments" name="comments" rows="3">{{ old('comments') }}</textarea>
                        @error('comments')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('approval-workflows.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Workflow
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
        $('#loan_id, #approver_id').select2({
            theme: 'bootstrap-5'
        });
    }
});
</script>
@endpush
@endsection

