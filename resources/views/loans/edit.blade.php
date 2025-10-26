@extends('layouts.app')

@section('title', 'Edit Loan')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-edit me-2"></i>Edit Loan</h1>
    <p class="page-subtitle">Update loan details - {{ $loan->loan_number }}</p>
</div>

<form action="{{ route('loans.update', $loan) }}" method="POST" id="loanEditForm">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Loan Basic Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">Loan Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Loan Status</label>
                        <div class="alert alert-{{ $loan->status === 'pending' ? 'warning' : ($loan->status === 'approved' ? 'success' : 'info') }}">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>{{ ucfirst($loan->status) }}</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="client_id" class="form-label">Borrower <span class="text-danger">*</span></label>
                        <select class="form-select @error('client_id') is-invalid @enderror" 
                                id="client_id" name="client_id" required>
                            <option value="">Select Borrower</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ (old('client_id', $loan->client_id) == $client->id) ? 'selected' : '' }}>
                                    {{ $client->full_name }} ({{ $client->client_number }})
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Principal Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                           id="amount" name="amount" value="{{ old('amount', $loan->amount) }}" 
                                           step="0.01" min="100" max="1000000" required
                                           {{ in_array($loan->status, ['active', 'disbursed', 'completed']) ? 'readonly' : '' }}>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="interest_rate" class="form-label">Interest Rate (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('interest_rate') is-invalid @enderror" 
                                           id="interest_rate" name="interest_rate" value="{{ old('interest_rate', $loan->interest_rate) }}" 
                                           step="0.01" min="0" max="100" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('interest_rate')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="term_months" class="form-label">Loan Term <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('term_months') is-invalid @enderror" 
                                           id="term_months" name="term_months" value="{{ old('term_months', $loan->term_months) }}" 
                                           min="1" max="60" required>
                                    <span class="input-group-text">Months</span>
                                </div>
                                @error('term_months')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="pending" {{ old('status', $loan->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ old('status', $loan->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="active" {{ old('status', $loan->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="overdue" {{ old('status', $loan->status) === 'overdue' ? 'selected' : '' }}>Overdue</option>
                                    <option value="completed" {{ old('status', $loan->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="defaulted" {{ old('status', $loan->status) === 'defaulted' ? 'selected' : '' }}>Defaulted</option>
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
                                  id="purpose" name="purpose" rows="3" required>{{ old('purpose', $loan->loan_purpose ?? $loan->purpose) }}</textarea>
                        @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="collateral_description" class="form-label">Collateral Description</label>
                                <textarea class="form-control @error('collateral_description') is-invalid @enderror" 
                                          id="collateral_description" name="collateral_description" rows="2">{{ old('collateral_description') }}</textarea>
                                @error('collateral_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="collateral_value" class="form-label">Collateral Value</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('collateral_value') is-invalid @enderror" 
                                           id="collateral_value" name="collateral_value" value="{{ old('collateral_value', 0) }}" 
                                           step="0.01" min="0">
                                </div>
                                @error('collateral_value')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Summary -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Loan Summary</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Loan Number:</strong>
                        <p>{{ $loan->loan_number }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Current Outstanding:</strong>
                        <p class="text-danger">${{ number_format($loan->outstanding_balance ?? 0, 2) }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Total Paid:</strong>
                        <p class="text-success">${{ number_format($loan->total_paid ?? 0, 2) }}</p>
                    </div>
                    @if($loan->monthly_payment)
                    <div class="mb-3">
                        <strong>Monthly Payment:</strong>
                        <p>${{ number_format($loan->monthly_payment, 2) }}</p>
                    </div>
                    @endif
                    @if($loan->next_due_date)
                    <div class="mb-3">
                        <strong>Next Due Date:</strong>
                        <p>{{ $loan->next_due_date->format('M d, Y') }}</p>
                    </div>
                    @endif
                    <div class="mb-3">
                        <strong>Created:</strong>
                        <p>{{ $loan->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Client Info -->
            @if($loan->client)
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Client Information</h6>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong><br>{{ $loan->client->full_name }}</p>
                    <p><strong>Phone:</strong><br>{{ $loan->client->phone }}</p>
                    <p><strong>Email:</strong><br>{{ $loan->client->email ?? 'N/A' }}</p>
                    <a href="{{ route('clients.show', $loan->client) }}" class="btn btn-sm btn-success" target="_blank">
                        <i class="fas fa-user me-1"></i>View Client
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Form Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('loans.show', $loan) }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i>Update Loan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for better UX
    if (typeof $.fn.select2 !== 'undefined') {
        $('#client_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select Borrower',
            allowClear: true
        });
    }
});
</script>
@endpush
@endsection

