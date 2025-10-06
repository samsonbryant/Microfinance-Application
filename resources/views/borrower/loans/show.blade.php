@extends('layouts.app')

@section('title', 'Loan Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-file-alt me-2"></i>Loan #{{ $loan->id }}</h4>
                <div>
                    <a href="{{ route('borrower.loans.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Loans
                    </a>
                    @if($loan->status === 'active' || $loan->status === 'overdue')
                        <a href="{{ route('borrower.payments.create', ['loan_id' => $loan->id]) }}" class="btn btn-success">
                            <i class="fas fa-money-bill-wave me-2"></i>Make Payment
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Loan Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Loan Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Loan Amount:</strong></td>
                                    <td>${{ number_format($loan->principal_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Outstanding Balance:</strong></td>
                                    <td>${{ number_format($loan->outstanding_balance, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Interest Rate:</strong></td>
                                    <td>{{ number_format($loan->interest_rate, 2) }}%</td>
                                </tr>
                                <tr>
                                    <td><strong>Term:</strong></td>
                                    <td>{{ $loan->term_months }} months</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Monthly Payment:</strong></td>
                                    <td>${{ number_format($loan->monthly_payment, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Next Payment:</strong></td>
                                    <td>{{ $loan->next_payment_date ? $loan->next_payment_date->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $loan->status === 'active' ? 'success' : ($loan->status === 'overdue' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($loan->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Applied Date:</strong></td>
                                    <td>{{ $loan->created_at->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($loan->purpose)
                        <div class="mt-3">
                            <strong>Purpose:</strong>
                            <p class="mt-1">{{ $loan->purpose }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment History -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Payment History
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Principal</th>
                                    <th>Interest</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loan->repayments ?? [] as $repayment)
                                    <tr>
                                        <td>{{ $repayment->actual_payment_date ? $repayment->actual_payment_date->format('M d, Y') : 'N/A' }}</td>
                                        <td>${{ number_format($repayment->total_paid, 2) }}</td>
                                        <td>${{ number_format($repayment->principal_paid, 2) }}</td>
                                        <td>${{ number_format($repayment->interest_paid, 2) }}</td>
                                        <td>{{ ucfirst($repayment->payment_method ?? 'N/A') }}</td>
                                        <td>
                                            <span class="badge bg-success">Completed</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No payments made yet</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loan Summary -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-chart-pie me-2"></i>Loan Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Principal Paid:</span>
                            <strong>${{ number_format($loan->principal_amount - $loan->outstanding_balance, 2) }}</strong>
                        </div>
                        <div class="progress mt-1" style="height: 8px;">
                            <div class="progress-bar bg-success" 
                                 style="width: {{ $loan->principal_amount > 0 ? (($loan->principal_amount - $loan->outstanding_balance) / $loan->principal_amount) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Remaining Balance:</span>
                            <strong>${{ number_format($loan->outstanding_balance, 2) }}</strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Interest:</span>
                            <strong>${{ number_format($loan->total_interest ?? 0, 2) }}</strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Amount:</span>
                            <strong>${{ number_format($loan->principal_amount + ($loan->total_interest ?? 0), 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($loan->status === 'active' || $loan->status === 'overdue')
                            <a href="{{ route('borrower.payments.create', ['loan_id' => $loan->id]) }}" class="btn btn-success">
                                <i class="fas fa-money-bill-wave me-2"></i>Make Payment
                            </a>
                        @endif
                        
                        <a href="{{ route('borrower.transactions.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-list me-2"></i>View All Transactions
                        </a>
                        
                        <a href="{{ route('borrower.loans.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Loans
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
