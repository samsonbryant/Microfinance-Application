@extends('layouts.app')

@section('title', 'Process Approval')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-check-circle text-success me-2"></i>Process Approval
            </h1>
            <p class="text-muted mb-0">Approve or reject loan application</p>
        </div>
        <a href="{{ route('approval-workflows.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Loan Details -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Loan Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr><th width="200">Loan Number:</th><td>{{ $approvalWorkflow->loan->loan_number }}</td></tr>
                        <tr><th>Client:</th><td>{{ $approvalWorkflow->loan->client->full_name }}</td></tr>
                        <tr><th>Amount:</th><td>${{ number_format($approvalWorkflow->loan->amount, 2) }}</td></tr>
                        <tr><th>Term:</th><td>{{ $approvalWorkflow->loan->term_months }} months</td></tr>
                        <tr><th>Interest Rate:</th><td>{{ $approvalWorkflow->loan->interest_rate }}%</td></tr>
                        <tr><th>Purpose:</th><td>{{ $approvalWorkflow->loan->loan_purpose }}</td></tr>
                    </table>
                </div>
            </div>

            <!-- Approval Form -->
            <div class="card shadow">
                <div class="card-header bg-warning">
                    <h6 class="m-0 font-weight-bold">Approval Decision (Level {{ $approvalWorkflow->level }})</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('approval-workflows.update', $approvalWorkflow) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="status" class="form-label">Decision <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="comments" class="form-label">Comments</label>
                            <textarea class="form-control @error('comments') is-invalid @enderror" 
                                      id="comments" name="comments" rows="4">{{ old('comments', $approvalWorkflow->comments) }}</textarea>
                            @error('comments')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('approval-workflows.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1"></i>Submit Decision
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Workflow Information</h6>
                </div>
                <div class="card-body">
                    <p><strong>Approval Level:</strong><br>{{ $approvalWorkflow->level }}</p>
                    <p><strong>Assigned To:</strong><br>{{ $approvalWorkflow->approver->name }}</p>
                    <p><strong>Current Status:</strong><br>
                        <span class="badge bg-{{ $approvalWorkflow->status === 'approved' ? 'success' : ($approvalWorkflow->status === 'rejected' ? 'danger' : 'warning') }}">
                            {{ ucfirst($approvalWorkflow->status) }}
                        </span>
                    </p>
                    <p><strong>Created:</strong><br>{{ $approvalWorkflow->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

