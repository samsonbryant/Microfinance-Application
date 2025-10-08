@extends('layouts.app')

@section('title', 'Loan Application Details')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Loan Application #{{ $loanApplication->application_number }}</h1>
            <p class="page-subtitle">Application details and status</p>
        </div>
        <a href="{{ route('loan-applications.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to List
        </a>
    </div>
</div>

<div class="row">
    <!-- Application Details -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Application Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Client:</strong>
                        <p>{{ $loanApplication->client->full_name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Branch:</strong>
                        <p>{{ $loanApplication->branch->name ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Loan Type:</strong>
                        <p>{{ ucfirst($loanApplication->loan_type) }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Requested Amount:</strong>
                        <p>${{ number_format($loanApplication->requested_amount, 2) }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Term:</strong>
                        <p>{{ $loanApplication->term_months }} months</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Interest Rate:</strong>
                        <p>{{ $loanApplication->interest_rate }}%</p>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Purpose:</strong>
                    <p>{{ $loanApplication->purpose }}</p>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Employment Status:</strong>
                        <p>{{ ucfirst($loanApplication->employment_status ?? 'N/A') }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Monthly Income:</strong>
                        <p>${{ number_format($loanApplication->monthly_income ?? 0, 2) }}</p>
                    </div>
                </div>

                @if($loanApplication->collateral_type)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Collateral Type:</strong>
                            <p>{{ $loanApplication->collateral_type }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Collateral Value:</strong>
                            <p>${{ number_format($loanApplication->collateral_value ?? 0, 2) }}</p>
                        </div>
                    </div>
                @endif

                @if($loanApplication->rejection_reason)
                    <div class="alert alert-danger">
                        <strong>Rejection Reason:</strong>
                        <p class="mb-0">{{ $loanApplication->rejection_reason }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Status Card -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Status</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <span class="badge bg-{{ 
                        $loanApplication->status == 'pending' ? 'warning' : 
                        ($loanApplication->status == 'approved' ? 'success' : 
                        ($loanApplication->status == 'rejected' ? 'danger' : 'secondary')) 
                    }} fs-5 px-4 py-2">
                        {{ ucfirst($loanApplication->status) }}
                    </span>
                </div>

                <div class="mb-3">
                    <strong>Submitted:</strong>
                    <p>{{ $loanApplication->created_at->format('M d, Y H:i') }}</p>
                </div>

                <div class="mb-3">
                    <strong>Submitted By:</strong>
                    <p>{{ $loanApplication->createdBy->name ?? 'N/A' }}</p>
                </div>

                @if($loanApplication->status == 'pending')
                    <div class="d-grid gap-2">
                        <a href="{{ route('loan-applications.edit', $loanApplication) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Edit Application
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

