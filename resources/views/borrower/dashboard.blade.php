@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Dashboard</h1>
        <div class="btn-group">
            <a href="{{ route('borrower.payments.create') }}" class="btn btn-success">
                <i class="fas fa-credit-card"></i> Make Payment
            </a>
            <a href="{{ route('borrower.loans.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Apply for Loan
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">My Loans</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $loans->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Loans</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $loans->where('status', 'disbursed')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Outstanding Balance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($loans->where('status', 'disbursed')->sum('outstanding_balance'), 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Savings Balance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($savingsAccounts->sum('balance'), 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-piggy-bank fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Next Payment Due -->
    @php
        $nextPayment = $loans->where('status', 'disbursed')
            ->where('next_due_date', '>=', now())
            ->sortBy('next_due_date')
            ->first();
    @endphp

    @if($nextPayment)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-warning">
                <div class="card-header py-3 bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">Next Payment Due</h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-1">Payment Amount: ${{ number_format($nextPayment->next_payment_amount, 2) }}</h5>
                            <p class="mb-0 text-muted">Due Date: {{ $nextPayment->next_due_date->format('M d, Y') }}</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="{{ route('borrower.payments.create', ['loan_id' => $nextPayment->id]) }}" class="btn btn-warning btn-lg">
                                <i class="fas fa-credit-card"></i> Make Payment
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <a href="{{ route('borrower.loans.index') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-list"></i><br>
                                <small>My Loans</small>
                            </a>
                        </div>
                        <div class="col-md-2 mb-3">
                            <a href="{{ route('borrower.payments.create') }}" class="btn btn-success btn-block">
                                <i class="fas fa-credit-card"></i><br>
                                <small>Make Payment</small>
                            </a>
                        </div>
                        <div class="col-md-2 mb-3">
                            <a href="{{ route('borrower.savings.index') }}" class="btn btn-info btn-block">
                                <i class="fas fa-piggy-bank"></i><br>
                                <small>My Savings</small>
                            </a>
                        </div>
                        <div class="col-md-2 mb-3">
                            <a href="{{ route('borrower.transactions.index') }}" class="btn btn-warning btn-block">
                                <i class="fas fa-history"></i><br>
                                <small>Transactions</small>
                            </a>
                        </div>
                        <div class="col-md-2 mb-3">
                            <a href="{{ route('borrower.profile') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-user"></i><br>
                                <small>My Profile</small>
                            </a>
                        </div>
                        <div class="col-md-2 mb-3">
                            <a href="{{ route('notifications.index') }}" class="btn btn-dark btn-block">
                                <i class="fas fa-bell"></i><br>
                                <small>Notifications</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">My Loans</h6>
                </div>
                <div class="card-body">
                    @if($loans->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loans->take(5) as $loan)
                                    <tr>
                                        <td>${{ number_format($loan->amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $loan->status === 'disbursed' ? 'success' : ($loan->status === 'pending' ? 'warning' : 'info') }}">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $loan->next_due_date ? $loan->next_due_date->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('borrower.loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            <a href="{{ route('borrower.loans.index') }}" class="btn btn-primary">View All Loans</a>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No loans yet</h5>
                            <p class="text-muted">Apply for your first loan to get started.</p>
                            <a href="{{ route('borrower.loans.create') }}" class="btn btn-primary">Apply for Loan</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6>
                </div>
                <div class="card-body">
                    @if($recentTransactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTransactions as $transaction)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $transaction->type === 'deposit' ? 'success' : ($transaction->type === 'withdrawal' ? 'warning' : 'info') }}">
                                                {{ ucfirst($transaction->type) }}
                                            </span>
                                        </td>
                                        <td>${{ number_format($transaction->amount, 2) }}</td>
                                        <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            <a href="{{ route('borrower.transactions.index') }}" class="btn btn-primary">View All Transactions</a>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No transactions yet</h5>
                            <p class="text-muted">Your transaction history will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
