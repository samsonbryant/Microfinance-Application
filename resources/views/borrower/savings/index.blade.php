@extends('layouts.app')

@section('title', 'My Savings')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-piggy-bank me-2"></i>My Savings Accounts</h4>
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAccountModal">
                    <i class="fas fa-plus me-2"></i>Open New Account
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
                                    <th>Account Number</th>
                                    <th>Account Type</th>
                                    <th>Balance</th>
                                    <th>Interest Rate</th>
                                    <th>Status</th>
                                    <th>Last Transaction</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($savingsAccounts as $account)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-credit-card text-success me-2"></i>
                                                {{ $account->account_number ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>{{ ucfirst($account->account_type ?? 'Regular') }}</td>
                                        <td>
                                            <strong class="text-success">${{ number_format($account->balance, 2) }}</strong>
                                        </td>
                                        <td>{{ number_format($account->interest_rate ?? 0, 2) }}%</td>
                                        <td>
                                            <span class="badge bg-{{ $account->status === 'active' ? 'success' : 'warning' }}">
                                                {{ ucfirst($account->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $account->updated_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('borrower.savings.show', $account) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-piggy-bank fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No savings accounts found</p>
                                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAccountModal">
                                                Open Your First Savings Account
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($savingsAccounts->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $savingsAccounts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Account Modal -->
<div class="modal fade" id="newAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Open New Savings Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    To open a new savings account, please visit our nearest branch or contact our customer service.
                </div>
                <p>You can also call us at <strong>+1-800-SAVINGS</strong> for assistance.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="tel:+18007228464" class="btn btn-primary">Call Now</a>
            </div>
        </div>
    </div>
</div>
@endsection
