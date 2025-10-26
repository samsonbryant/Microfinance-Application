@extends('layouts.app')

@section('title', 'Savings Account Details')

@section('content')
<div class="container-fluid" style="font-family: 'Inter', sans-serif;">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-piggy-bank text-success"></i> Savings Account Details
            </h2>
            <p class="text-muted mb-0">Account #{{ $savingsAccount->account_number }}</p>
        </div>
        <a href="{{ route('borrower.savings.index') }}" class="btn btn-secondary" style="border-radius: 8px;">
            <i class="fas fa-arrow-left"></i> Back to Savings
        </a>
    </div>

    <!-- Account Summary Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <h6 class="mb-2 opacity-75">Current Balance</h6>
                    <h2 class="mb-0 fw-bold">${{ number_format($savingsAccount->balance, 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <h6 class="text-muted">Account Type</h6>
                    <p class="mb-0 fw-bold">{{ ucfirst($savingsAccount->account_type ?? 'Savings') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <h6 class="text-muted">Status</h6>
                    <span class="badge bg-{{ $savingsAccount->status === 'active' ? 'success' : 'secondary' }}">
                        {{ ucfirst($savingsAccount->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <div class="card-header bg-white border-0 pt-4">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-history text-primary"></i> Transaction History
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Balance After</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($savingsAccount->transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <span class="badge bg-{{ $transaction->type === 'deposit' ? 'success' : 'warning' }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td class="fw-bold">
                                @if($transaction->type === 'deposit')
                                    <span class="text-success">+${{ number_format($transaction->amount, 2) }}</span>
                                @else
                                    <span class="text-danger">-${{ number_format($transaction->amount, 2) }}</span>
                                @endif
                            </td>
                            <td>${{ number_format($transaction->balance_after ?? 0, 2) }}</td>
                            <td class="small">{{ $transaction->description ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No transactions yet</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

