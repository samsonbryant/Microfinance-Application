@extends('layouts.app')

@section('title', 'Borrower Dashboard')

@section('content')
<!-- Borrower Dashboard - Self-Service Portal -->
<div class="container-fluid">
    <!-- Welcome Section with Profile Summary -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow border-0 bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            @if(auth()->user()->client && auth()->user()->client->avatar)
                                <img src="{{ Storage::url(auth()->user()->client->avatar) }}" 
                                     alt="Avatar" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid white;">
                            @else
                                <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px; font-size: 2rem;">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="mb-1"><strong>Welcome, {{ auth()->user()->name }}!</strong></h3>
                            @if(auth()->user()->client)
                                <p class="mb-1">
                                    <i class="fas fa-id-card me-2"></i>
                                    Client ID: <strong>{{ auth()->user()->client->client_number }}</strong>
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-briefcase me-2"></i>
                                    {{ ucfirst(auth()->user()->client->occupation ?? 'N/A') }}
                                    @if(auth()->user()->client->employer)
                                        at {{ auth()->user()->client->employer }}
                                    @endif
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-phone me-2"></i>
                                    {{ auth()->user()->client->phone }} | 
                                    <i class="fas fa-envelope me-2 ms-2"></i>
                                    {{ auth()->user()->client->email }}
                                </p>
                            @else
                                <p class="mb-0">Manage your loans, savings, and payments easily</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow border-0">
                <div class="card-body">
                    <h6 class="mb-3"><i class="fas fa-user-check me-2 text-primary"></i>Account Status</h6>
                    @if(auth()->user()->client)
                        <div class="mb-2">
                            <small class="text-muted">KYC Status:</small>
                            <span class="badge bg-{{ auth()->user()->client->kyc_status === 'verified' ? 'success' : 'warning' }} float-end">
                                {{ ucfirst(auth()->user()->client->kyc_status) }}
                            </span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Account Status:</small>
                            <span class="badge bg-{{ auth()->user()->client->status === 'active' ? 'success' : 'secondary' }} float-end">
                                {{ ucfirst(auth()->user()->client->status) }}
                            </span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Member Since:</small>
                            <span class="float-end"><strong>{{ auth()->user()->client->created_at->format('M Y') }}</strong></span>
                        </div>
                        <div>
                            <small class="text-muted">Branch:</small>
                            <span class="float-end"><strong>{{ auth()->user()->client->branch->name ?? 'N/A' }}</strong></span>
                        </div>
                    @else
                        <p class="text-muted">No client profile linked</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Account Summary -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="stat-value">{{ $stats['active_loans'] ?? 0 }}</div>
                    <div class="stat-label">Active Loans</div>
                    <div class="mt-2">
                        <small class="opacity-75">Total: ${{ number_format($stats['total_loan_amount'] ?? 0, 2) }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                    <div class="stat-value">${{ number_format($stats['savings_balance'] ?? 0, 2) }}</div>
                    <div class="stat-label">Savings Balance</div>
                    <div class="mt-2">
                        <small class="opacity-75">Accounts: {{ $stats['savings_accounts'] ?? 0 }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-value">{{ $stats['upcoming_payments'] ?? 0 }}</div>
                    <div class="stat-label">Upcoming Payments</div>
                    <div class="mt-2">
                        <small class="opacity-75">Next 30 days</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-value">{{ $stats['credit_score'] ?? 'N/A' }}</div>
                    <div class="stat-label">Credit Score</div>
                    <div class="mt-2">
                        <small class="opacity-75">Last updated: {{ $stats['last_credit_update'] ?? 'Never' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- My Active Loans -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-hand-holding-usd me-2"></i>My Active Loans
                    </h6>
                    <a href="{{ route('borrower.loans.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Loan ID</th>
                                    <th>Amount</th>
                                    <th>Outstanding</th>
                                    <th>Next Payment</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myLoans ?? [] as $loan)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-alt text-primary me-2"></i>
                                                #{{ $loan->id }}
                                            </div>
                                        </td>
                                        <td>${{ number_format($loan->amount ?? $loan->principal_amount ?? 0, 2) }}</td>
                                        <td>${{ number_format($loan->outstanding_balance ?? 0, 2) }}</td>
                                        <td>${{ number_format($loan->calculateMonthlyPayment() ?? 0, 2) }}</td>
                                        <td>{{ $loan->getNextPaymentDue() ? $loan->getNextPaymentDue()->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $loan->status === 'active' ? 'success' : ($loan->status === 'overdue' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('borrower.loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No active loans</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- My Savings Accounts -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-piggy-bank me-2"></i>My Savings Accounts
                    </h6>
                    <a href="{{ route('borrower.savings.index') }}" class="btn btn-sm btn-success">View All</a>
                </div>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mySavings ?? [] as $savings)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-credit-card text-success me-2"></i>
                                                {{ $savings->account_number ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>{{ ucfirst($savings->account_type ?? 'Regular') }}</td>
                                        <td>${{ number_format($savings->balance, 2) }}</td>
                                        <td>{{ number_format($savings->interest_rate ?? 0, 2) }}%</td>
                                        <td>
                                            <span class="badge bg-{{ $savings->status === 'active' ? 'success' : 'warning' }}">
                                                {{ ucfirst($savings->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('borrower.savings.show', $savings) }}" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No savings accounts</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Recent Payment History
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Loan ID</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayments ?? [] as $payment)
                                    <tr>
                                        <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $payment->type === 'loan_payment' ? 'primary' : 'success' }}">
                                                {{ ucfirst(str_replace('_', ' ', $payment->type)) }}
                                            </span>
                                        </td>
                                        <td>${{ number_format($payment->amount, 2) }}</td>
                                        <td>#{{ $payment->loan_id ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-success">Completed</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No payment history</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('borrower.loans.create') }}" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i>Apply for Loan
                        </a>
                        <a href="{{ route('borrower.payments.create') }}" class="btn btn-outline-success">
                            <i class="fas fa-money-bill-wave me-2"></i>Make Payment
                        </a>
                        <a href="{{ route('borrower.savings.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-piggy-bank me-2"></i>View Savings
                        </a>
                        <a href="{{ route('borrower.profile') }}" class="btn btn-outline-warning">
                            <i class="fas fa-user-edit me-2"></i>Update Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loan Application Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-alt me-2"></i>Recent Loan Applications
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Application ID</th>
                                    <th>Amount Requested</th>
                                    <th>Purpose</th>
                                    <th>Applied Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myApplications ?? [] as $application)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-alt text-primary me-2"></i>
                                                #{{ $application->id }}
                                            </div>
                                        </td>
                                        <td>${{ number_format($application->requested_amount, 2) }}</td>
                                        <td>{{ $application->purpose ?? 'N/A' }}</td>
                                        <td>{{ $application->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $application->status === 'approved' ? 'success' : ($application->status === 'rejected' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($application->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('borrower.loans.show', $application) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No loan applications</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}

.stat-icon {
    font-size: 2.5rem;
    opacity: 0.8;
    float: right;
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    margin-top: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
    margin-top: 0.5rem;
}
</style>

@section('scripts')
<script>
// Any borrower-specific JavaScript can go here
console.log('Borrower dashboard loaded');
</script>
@endsection