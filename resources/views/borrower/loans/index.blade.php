@extends('layouts.app')

@section('title', 'My Loans')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-hand-holding-usd me-2"></i>My Loans</h4>
                <a href="{{ route('borrower.loans.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Apply for New Loan
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Loan ID</th>
                                    <th>Amount</th>
                                    <th>Outstanding</th>
                                    <th>Monthly Payment</th>
                                    <th>Next Payment</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loans as $loan)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-alt text-primary me-2"></i>
                                                #{{ $loan->id }}
                                            </div>
                                        </td>
                                        <td>${{ number_format($loan->principal_amount, 2) }}</td>
                                        <td>${{ number_format($loan->outstanding_balance, 2) }}</td>
                                        <td>${{ number_format($loan->monthly_payment, 2) }}</td>
                                        <td>{{ $loan->next_payment_date ? $loan->next_payment_date->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $loan->status === 'active' ? 'success' : ($loan->status === 'overdue' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('borrower.loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            @if($loan->status === 'active' || $loan->status === 'overdue')
                                                <a href="{{ route('borrower.payments.create', ['loan_id' => $loan->id]) }}" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-money-bill-wave"></i> Pay
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No loans found</p>
                                            <a href="{{ route('borrower.loans.create') }}" class="btn btn-primary">
                                                Apply for Your First Loan
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($loans->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $loans->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
