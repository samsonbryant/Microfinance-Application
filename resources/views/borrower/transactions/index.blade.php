@extends('layouts.app')

@section('title', 'My Transactions')

@section('content')
<div class="container-fluid" style="font-family: 'Inter', sans-serif;">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-exchange-alt text-info"></i> My Transactions
            </h2>
            <p class="text-muted mb-0">View your complete transaction history</p>
        </div>
        <a href="{{ route('borrower.dashboard') }}" class="btn btn-secondary" style="border-radius: 8px;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Transactions Card -->
    <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                            <td>
                                @php
                                    $typeColors = [
                                        'deposit' => 'success',
                                        'withdrawal' => 'warning',
                                        'loan_payment' => 'primary',
                                        'loan_repayment' => 'primary',
                                        'repayment' => 'primary',
                                        'interest_posting' => 'info',
                                        'fee' => 'secondary',
                                        'penalty' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $typeColors[$transaction->type] ?? 'info' }}">
                                    {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                </span>
                            </td>
                            <td>{{ $transaction->description ?? 'N/A' }}</td>
                            <td class="fw-bold">
                                @if(in_array($transaction->type, ['deposit', 'loan_payment', 'repayment']))
                                    <span class="text-success">+${{ number_format($transaction->amount, 2) }}</span>
                                @else
                                    <span class="text-danger">-${{ number_format($transaction->amount, 2) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                @if($transaction->loan)
                                    Loan #{{ $transaction->loan->id }}
                                @elseif($transaction->savingsAccount)
                                    Savings #{{ $transaction->savingsAccount->id }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-history fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No transactions yet</h5>
                                <p class="text-muted">Your transaction history will appear here.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $transactions->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

