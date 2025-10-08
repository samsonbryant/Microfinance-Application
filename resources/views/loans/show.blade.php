@extends('layouts.app')

@section('title', 'Loan Details')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title"><i class="fas fa-hand-holding-usd me-2"></i>Loan #{{ $loan->loan_number }}</h1>
            <p class="page-subtitle">Complete loan information and history</p>
        </div>
        <div>
            <a href="{{ route('loans.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Loans
            </a>
            <a href="{{ route('loans.edit', $loan) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>Edit Loan
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Loan Information -->
    <div class="col-lg-8">
        <!-- Basic Details -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">Loan Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted">Loan Number:</label>
                        <p class="fs-5"><strong>{{ $loan->loan_number }}</strong></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted">Status:</label>
                        <p>
                            <span class="badge bg-{{ 
                                $loan->status == 'active' ? 'success' : 
                                ($loan->status == 'pending' ? 'warning' : 
                                ($loan->status == 'overdue' ? 'danger' : 'info')) 
                            }} fs-6 px-3 py-2">
                                {{ ucfirst($loan->status) }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted">Borrower:</label>
                        <p>
                            <a href="{{ route('clients.show', $loan->client) }}">
                                {{ $loan->client->full_name }}
                            </a>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted">Branch:</label>
                        <p>{{ $loan->branch->name ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted">Principal Amount:</label>
                        <p class="text-primary fs-4"><strong>${{ number_format($loan->amount, 2) }}</strong></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted">Interest Rate:</label>
                        <p class="fs-5"><strong>{{ number_format($loan->interest_rate, 2) }}%</strong></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted">Term:</label>
                        <p class="fs-5"><strong>{{ $loan->term_months }} {{ $loan->duration_period ?? 'months' }}</strong></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted">Interest Method:</label>
                        <p>{{ ucfirst(str_replace('_', ' ', $loan->interest_method ?? 'flat')) }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted">Repayment Type:</label>
                        <p>{{ ucfirst(str_replace('_', ' ', $loan->repayment_type ?? 'standard')) }}</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted">Release Date:</label>
                        <p>{{ $loan->release_date ? $loan->release_date->format('M d, Y') : ($loan->disbursement_date ? $loan->disbursement_date->format('M d, Y') : 'Not disbursed') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted">Due Date:</label>
                        <p>{{ $loan->due_date ? $loan->due_date->format('M d, Y') : 'N/A' }}</p>
                    </div>
                </div>

                @if($loan->credit_risk_score)
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Credit Risk Score:</label>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-{{ $loan->credit_risk_score > 70 ? 'success' : ($loan->credit_risk_score > 40 ? 'warning' : 'danger') }}" 
                                 role="progressbar" style="width: {{ $loan->credit_risk_score }}%">
                                <strong>{{ number_format($loan->credit_risk_score, 0) }}</strong>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-success text-white">
                <h6 class="m-0 font-weight-bold">Financial Summary</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="p-3">
                            <h4 class="text-primary">${{ number_format($loan->amount, 2) }}</h4>
                            <p class="text-muted small mb-0">Principal Amount</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <h4 class="text-success">${{ number_format($loan->total_paid ?? 0, 2) }}</h4>
                            <p class="text-muted small mb-0">Total Paid</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <h4 class="text-warning">${{ number_format($loan->outstanding_balance ?? 0, 2) }}</h4>
                            <p class="text-muted small mb-0">Outstanding Balance</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <h4 class="text-info">${{ number_format($loan->calculateMonthlyPayment(), 2) }}</h4>
                            <p class="text-muted small mb-0">Monthly Payment</p>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Total Interest:</strong> ${{ number_format($loan->calculateTotalInterest(), 2) }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Next Payment Due:</strong> {{ $loan->getNextPaymentDue() ? $loan->getNextPaymentDue()->format('M d, Y') : 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collateral Information -->
        @if($loan->collateral)
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Collateral Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Type:</label>
                            <p>{{ $loan->collateral->type }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Estimated Value:</label>
                            <p class="text-success fs-5">${{ number_format($loan->collateral->estimated_value, 2) }}</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Description:</label>
                        <p>{{ $loan->collateral->description }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Condition:</label>
                        <p>{{ $loan->collateral->condition }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Loan Fees -->
        @if($loan->fees && $loan->fees->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning">
                    <h6 class="m-0 font-weight-bold">Loan Fees</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fee Name</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Charge Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loan->fees as $fee)
                                    <tr>
                                        <td>{{ $fee->fee_name }}</td>
                                        <td><span class="badge bg-secondary">{{ ucfirst($fee->fee_type) }}</span></td>
                                        <td>${{ number_format($fee->fee_amount, 2) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $fee->charge_type)) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Payment History -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Payment History</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transaction #</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Balance After</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loan->transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                    <td>{{ $transaction->transaction_number }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->type == 'loan_repayment' ? 'success' : 'info' }}">
                                            {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                        </span>
                                    </td>
                                    <td>${{ number_format($transaction->amount, 2) }}</td>
                                    <td>${{ number_format($transaction->balance_after ?? 0, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No payment history</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Actions -->
    <div class="col-lg-4">
        <!-- Actions Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-dark text-white">
                <h6 class="m-0 font-weight-bold">Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($loan->status === 'pending')
                        <form action="{{ route('loans.approve', $loan) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approve this loan?')">
                                <i class="fas fa-check me-1"></i>Approve Loan
                            </button>
                        </form>
                        <form action="{{ route('loans.reject', $loan) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Reject this loan?')">
                                <i class="fas fa-times me-1"></i>Reject Loan
                            </button>
                        </form>
                    @endif

                    @if($loan->status === 'approved')
                        <form action="{{ route('loans.disburse', $loan) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-info w-100" onclick="return confirm('Disburse this loan?')">
                                <i class="fas fa-money-bill-wave me-1"></i>Disburse Loan
                            </button>
                        </form>
                    @endif

                    @if(in_array($loan->status, ['active', 'overdue']))
                        <a href="{{ route('loan-repayments.create') }}?loan_id={{ $loan->id }}" class="btn btn-primary w-100">
                            <i class="fas fa-credit-card me-1"></i>Record Payment
                        </a>
                    @endif

                    <a href="{{ route('loans.edit', $loan) }}" class="btn btn-outline-warning w-100">
                        <i class="fas fa-edit me-1"></i>Edit Loan
                    </a>

                    <form action="{{ route('loans.destroy', $loan) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this loan? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-1"></i>Delete Loan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Loan Stats -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Loan Statistics</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Total Repayments:</small>
                    <h5 class="text-success">${{ number_format($stats['total_repayments'] ?? 0, 2) }}</h5>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Remaining Balance:</small>
                    <h5 class="text-warning">${{ number_format($stats['remaining_balance'] ?? 0, 2) }}</h5>
                </div>
                @if($stats['days_overdue'] ?? 0 > 0)
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>{{ $stats['days_overdue'] }} days overdue</strong>
                    </div>
                @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Payment on track</strong>
                    </div>
                @endif
            </div>
        </div>

        <!-- Loan Details -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Additional Details</h6>
            </div>
            <div class="card-body">
                <p><small class="text-muted">Created By:</small><br>
                <strong>{{ $loan->createdBy->name ?? 'System' }}</strong></p>
                
                <p><small class="text-muted">Created On:</small><br>
                <strong>{{ $loan->created_at->format('M d, Y H:i') }}</strong></p>

                @if($loan->credit_risk_score)
                    <p><small class="text-muted">Risk Score:</small><br>
                    <strong>{{ number_format($loan->credit_risk_score, 0) }}/100</strong></p>
                @endif

                @if($loan->late_penalty_enabled)
                    <p><small class="text-muted">Late Penalty:</small><br>
                    <strong>${{ number_format($loan->late_penalty_amount, 2) }} ({{ ucfirst($loan->late_penalty_type) }})</strong></p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

