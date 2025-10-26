@extends('layouts.app')

@section('title', 'Approval Workflow Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-tasks text-primary me-2"></i>Approval Workflow Details
            </h1>
            <p class="text-muted mb-0">Level {{ $approvalWorkflow->level }} Approval</p>
        </div>
        <div class="btn-group">
            @if($approvalWorkflow->status === 'pending' && $approvalWorkflow->approver_id === auth()->id())
                <a href="{{ route('approval-workflows.edit', $approvalWorkflow) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i>Process Approval
                </a>
            @endif
            <a href="{{ route('approval-workflows.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Loan Details -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Loan Application Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr><th width="200">Loan Number:</th><td><strong>{{ $approvalWorkflow->loan->loan_number }}</strong></td></tr>
                        <tr><th>Client:</th><td>{{ $approvalWorkflow->loan->client->full_name }}</td></tr>
                        <tr><th>Amount:</th><td class="text-primary"><strong>${{ number_format($approvalWorkflow->loan->amount, 2) }}</strong></td></tr>
                        <tr><th>Term:</th><td>{{ $approvalWorkflow->loan->term_months }} months</td></tr>
                        <tr><th>Interest Rate:</th><td>{{ $approvalWorkflow->loan->interest_rate }}%</td></tr>
                        <tr><th>Purpose:</th><td>{{ $approvalWorkflow->loan->loan_purpose }}</td></tr>
                        <tr><th>Application Date:</th><td>{{ $approvalWorkflow->loan->created_at->format('M d, Y H:i') }}</td></tr>
                    </table>
                </div>
            </div>

            <!-- Comments -->
            @if($approvalWorkflow->comments)
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="m-0 font-weight-bold">Comments</h6>
                </div>
                <div class="card-body">
                    <p>{{ $approvalWorkflow->comments }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Workflow Status -->
            <div class="card shadow mb-4">
                <div class="card-header bg-{{ $approvalWorkflow->status === 'approved' ? 'success' : ($approvalWorkflow->status === 'rejected' ? 'danger' : 'warning') }} text-white">
                    <h6 class="m-0 font-weight-bold">Workflow Status</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <span class="badge bg-{{ $approvalWorkflow->status === 'approved' ? 'success' : ($approvalWorkflow->status === 'rejected' ? 'danger' : 'warning') }}" style="font-size: 1.2rem;">
                            {{ ucfirst($approvalWorkflow->status) }}
                        </span>
                    </div>
                    <hr>
                    <p><strong>Approval Level:</strong><br>Level {{ $approvalWorkflow->level }}</p>
                    <p><strong>Assigned To:</strong><br>{{ $approvalWorkflow->approver->name }}</p>
                    <p><strong>Created:</strong><br>{{ $approvalWorkflow->created_at->format('M d, Y H:i') }}</p>
                    @if($approvalWorkflow->reviewed_at)
                        <p><strong>Reviewed:</strong><br>{{ $approvalWorkflow->reviewed_at->format('M d, Y H:i') }}</p>
                    @endif
                </div>
            </div>

            <!-- Client Info -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Client Information</h6>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong><br>{{ $approvalWorkflow->loan->client->full_name }}</p>
                    <p><strong>Phone:</strong><br>{{ $approvalWorkflow->loan->client->phone }}</p>
                    <p><strong>Email:</strong><br>{{ $approvalWorkflow->loan->client->email ?? 'N/A' }}</p>
                    <a href="{{ route('clients.show', $approvalWorkflow->loan->client) }}" class="btn btn-sm btn-info" target="_blank">
                        <i class="fas fa-user me-1"></i>View Client
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

