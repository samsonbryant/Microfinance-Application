@extends('layouts.app')

@section('title', 'Repayment Details')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Repayment #{{ $loanRepayment->transaction_number }}</h1>
            <p class="page-subtitle">Transaction details</p>
        </div>
        <a href="{{ route('loan-repayments.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Repayment Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Transaction Number:</strong>
                        <p>{{ $loanRepayment->transaction_number }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p>
                            <span class="badge bg-{{ $loanRepayment->status == 'completed' ? 'success' : 'warning' }}">
                                {{ ucfirst($loanRepayment->status) }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Client:</strong>
                        <p>{{ $loanRepayment->client->full_name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Loan:</strong>
                        <p>{{ $loanRepayment->loan->loan_number ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Amount Paid:</strong>
                        <p class="text-success fs-4">${{ number_format($loanRepayment->amount, 2) }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Payment Date:</strong>
                        <p>{{ $loanRepayment->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Branch:</strong>
                        <p>{{ $loanRepayment->branch->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Processed By:</strong>
                        <p>{{ $loanRepayment->createdBy->name ?? 'System' }}</p>
                    </div>
                </div>

                @if($loanRepayment->reference_number)
                    <div class="mb-3">
                        <strong>Reference Number:</strong>
                        <p>{{ $loanRepayment->reference_number }}</p>
                    </div>
                @endif

                @if($loanRepayment->description)
                    <div class="mb-3">
                        <strong>Description:</strong>
                        <p>{{ $loanRepayment->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Loan Status Card -->
    <div class="col-lg-4">
        @if($loanRepayment->loan)
            <div class="card shadow bg-light">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Loan Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Outstanding Balance:</strong>
                        <p class="fs-5 text-danger">${{ number_format($loanRepayment->loan->outstanding_balance, 2) }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Total Paid:</strong>
                        <p class="fs-5 text-success">${{ number_format($loanRepayment->loan->total_paid, 2) }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Original Amount:</strong>
                        <p>${{ number_format($loanRepayment->loan->amount, 2) }}</p>
                    </div>
                    <div>
                        <strong>Loan Status:</strong>
                        <p>
                            <span class="badge bg-{{ $loanRepayment->loan->status == 'active' ? 'success' : 'info' }}">
                                {{ ucfirst($loanRepayment->loan->status) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

