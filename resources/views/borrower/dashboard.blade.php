@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-circle text-success me-2"></i>Welcome, {{ auth()->user()->name }}!
            </h1>
            <p class="text-muted mb-0">Your Personal Financial Dashboard</p>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-info" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <a href="{{ route('borrower.payments.create') }}" class="btn btn-success">
                <i class="fas fa-credit-card"></i> Make Payment
            </a>
            <a href="{{ route('borrower.loans.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Apply for Loan
            </a>
        </div>
    </div>

    <!-- Real-time Status Bar -->
    <div class="alert alert-success d-flex align-items-center justify-content-between mb-4">
        <div>
            <i class="fas fa-circle text-success me-2 pulse-animation"></i>
            <strong>Live Account Updates:</strong> Last refreshed <span id="last-update-time">just now</span>
        </div>
        <small class="text-muted">Auto-updates every 30 seconds</small>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- My Financial Summary -->
    <div class="row g-3 mb-4">
        <!-- My Loans -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">My Loans</div>
                            <div class="stat-value" id="my-loans-count">
                                {{ $loans->count() }}
                            </div>
                            <div class="stat-change">
                                <i class="fas fa-check-circle"></i>
                                {{ $loans->where('status', 'disbursed')->count() }} Active
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-hand-holding-usd fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outstanding Balance -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Outstanding Balance</div>
                            <div class="stat-value" id="my-outstanding">
                                ${{ number_format($loans->where('status', 'disbursed')->sum('outstanding_balance'), 0) }}
                            </div>
                            <div class="stat-change">
                                <i class="fas fa-dollar-sign"></i>
                                Total Owed
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-wallet fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Savings Balance -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Savings Balance</div>
                            <div class="stat-value" id="my-savings">
                                ${{ number_format($savingsAccounts->sum('balance'), 2) }}
                            </div>
                            <div class="stat-change">
                                <i class="fas fa-piggy-bank"></i>
                                {{ $savingsAccounts->count() }} Account(s)
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-piggy-bank fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Next Payment -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Next Payment</div>
                            @php
                                $nextPayment = $loans->where('status', 'disbursed')
                                    ->where('next_due_date', '>=', now())
                                    ->sortBy('next_due_date')
                                    ->first();
                            @endphp
                            <div class="stat-value" id="next-payment-amount">
                                @if($nextPayment)
                                    ${{ number_format($nextPayment->next_payment_amount, 0) }}
                                @else
                                    $0
                                @endif
                            </div>
                            <div class="stat-change">
                                <i class="fas fa-calendar-alt"></i>
                                @if($nextPayment)
                                    {{ $nextPayment->next_due_date->format('M d, Y') }}
                                @else
                                    No payments due
                                @endif
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-clock fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Next Payment Alert -->
    @if($nextPayment)
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card shadow border-warning">
                <div class="card-header py-3 bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i>Upcoming Payment Alert
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-1">Payment Due: ${{ number_format($nextPayment->next_payment_amount, 2) }}</h5>
                            <p class="mb-0 text-muted">
                                <i class="fas fa-calendar me-2"></i>Due Date: {{ $nextPayment->next_due_date->format('l, F d, Y') }}
                                <span class="badge bg-{{ $nextPayment->next_due_date->diffInDays(now()) <= 3 ? 'danger' : 'warning' }} ms-2">
                                    {{ $nextPayment->next_due_date->diffInDays(now()) }} days remaining
                                </span>
                            </p>
                            <p class="mb-0 text-muted mt-2">
                                <i class="fas fa-file-alt me-2"></i>Loan: {{ $nextPayment->loan_number }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('borrower.payments.create', ['loan_id' => $nextPayment->id]) }}" class="btn btn-warning btn-lg">
                                <i class="fas fa-credit-card me-2"></i>Pay Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-2 col-6">
                            <a href="{{ route('borrower.loans.index') }}" class="quick-action-btn btn btn-outline-primary w-100">
                                <i class="fas fa-list fa-2x mb-2"></i>
                                <div>My Loans</div>
                            </a>
                        </div>
                        <div class="col-md-2 col-6">
                            <a href="{{ route('borrower.loans.create') }}" class="quick-action-btn btn btn-outline-success w-100">
                                <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                <div>Apply for Loan</div>
                            </a>
                        </div>
                        <div class="col-md-2 col-6">
                            <a href="{{ route('borrower.payments.create') }}" class="quick-action-btn btn btn-outline-warning w-100">
                                <i class="fas fa-credit-card fa-2x mb-2"></i>
                                <div>Make Payment</div>
                            </a>
                        </div>
                        <div class="col-md-2 col-6">
                            <a href="{{ route('borrower.savings.index') }}" class="quick-action-btn btn btn-outline-info w-100">
                                <i class="fas fa-piggy-bank fa-2x mb-2"></i>
                                <div>My Savings</div>
                            </a>
                        </div>
                        <div class="col-md-2 col-6">
                            <a href="{{ route('borrower.transactions.index') }}" class="quick-action-btn btn btn-outline-secondary w-100">
                                <i class="fas fa-history fa-2x mb-2"></i>
                                <div>Transactions</div>
                            </a>
                        </div>
                        <div class="col-md-2 col-6">
                            <a href="{{ route('borrower.profile') }}" class="quick-action-btn btn btn-outline-dark w-100">
                                <i class="fas fa-user fa-2x mb-2"></i>
                                <div>My Profile</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- My Loans & Transactions -->
    <div class="row g-3 mb-4">
        <!-- My Active Loans -->
        <div class="col-lg-6">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-briefcase me-2"></i>My Active Loans
                        <span class="badge bg-light text-dark ms-2">{{ $loans->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($loans->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Loan #</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loans->take(5) as $loan)
                                    <tr>
                                        <td><strong>{{ $loan->loan_number }}</strong></td>
                                        <td>${{ number_format($loan->amount, 0) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $loan->status === 'disbursed' ? 'success' : ($loan->status === 'pending' ? 'warning' : 'info') }}">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($loan->next_due_date)
                                                {{ $loan->next_due_date->format('M d, Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('borrower.loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($loans->count() > 5)
                        <div class="text-center mt-3">
                            <a href="{{ route('borrower.loans.index') }}" class="btn btn-primary">
                                View All {{ $loans->count() }} Loans
                            </a>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No loans yet</h5>
                            <p class="text-muted mb-3">Start your financial journey by applying for your first loan.</p>
                            <a href="{{ route('borrower.loans.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Apply for Loan
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="col-lg-6">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exchange-alt me-2"></i>Recent Transactions
                    </h6>
                </div>
                <div class="card-body">
                    @if($recentTransactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTransactions->take(5) as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->type === 'deposit' ? 'success' : ($transaction->type === 'withdrawal' ? 'warning' : 'info') }}">
                                                {{ ucfirst($transaction->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>${{ number_format($transaction->amount, 2) }}</strong>
                                        </td>
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
                        <div class="text-center mt-3">
                            <a href="{{ route('borrower.transactions.index') }}" class="btn btn-success">
                                View All Transactions
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No transactions yet</h5>
                            <p class="text-muted">Your transaction history will appear here once you start making transactions.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Charts -->
    <div class="row g-3 mb-4">
        <div class="col-xl-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>My Financial Overview
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="loanStatusChart"></canvas>
                        </div>
                        <div class="col-md-6">
                            <canvas id="balanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 12px rgba(0,0,0,0.15);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.stat-label {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    opacity: 0.9;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-change {
    font-size: 0.875rem;
    opacity: 0.9;
}

.stat-icon {
    opacity: 0.3;
}

.quick-action-btn {
    padding: 1.5rem 0.5rem;
    text-align: center;
    transition: all 0.3s ease;
    border-width: 2px;
}

.quick-action-btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.quick-action-btn i {
    display: block;
}

.quick-action-btn div {
    font-size: 0.875rem;
    font-weight: 600;
}

.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

@media (max-width: 768px) {
    .stat-value {
        font-size: 1.5rem;
    }
    
    .quick-action-btn {
        padding: 1rem 0.25rem;
    }
    
    .quick-action-btn i {
        font-size: 1.5rem;
    }
}
</style>
@endsection

@section('scripts')
<script>
// Real-time dashboard refresh
function refreshDashboard() {
    const refreshBtn = document.querySelector('button[onclick="refreshDashboard()"]');
    const icon = refreshBtn?.querySelector('i');
    
    if (icon) {
        icon.classList.add('fa-spin');
        refreshBtn.disabled = true;
    }
    
    fetch('{{ route("borrower.dashboard.realtime") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardStats(data.data);
                updateLastUpdateTime();
            }
        })
        .catch(error => console.error('Error:', error))
        .finally(() => {
            if (icon) {
                icon.classList.remove('fa-spin');
                refreshBtn.disabled = false;
            }
        });
}

function updateDashboardStats(data) {
    if (data.stats) {
        const stats = data.stats;
        
        if (document.getElementById('my-loans-count')) {
            document.getElementById('my-loans-count').textContent = stats.active_loans || 0;
        }
        if (document.getElementById('my-outstanding')) {
            document.getElementById('my-outstanding').textContent = '$' + formatNumber(stats.outstanding_balance || 0);
        }
        if (document.getElementById('my-savings')) {
            document.getElementById('my-savings').textContent = '$' + formatNumber(stats.savings_balance || 0);
        }
    }
    
    if (data.next_payment && document.getElementById('next-payment-amount')) {
        document.getElementById('next-payment-amount').textContent = '$' + formatNumber(data.next_payment.amount || 0);
    }
}

function updateLastUpdateTime() {
    const element = document.getElementById('last-update-time');
    if (element) {
        element.textContent = new Date().toLocaleTimeString();
    }
}

function formatNumber(num) {
    return new Intl.NumberFormat().format(Math.round(num));
}

// Auto-refresh every 30 seconds
let refreshInterval;
document.addEventListener('DOMContentLoaded', function() {
    refreshInterval = setInterval(refreshDashboard, 30000);
    initializeCharts();
});

function initializeCharts() {
    // Loan Status Chart
    const loanStatusCtx = document.getElementById('loanStatusChart');
    if (loanStatusCtx) {
        new Chart(loanStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active Loans', 'Pending', 'Completed'],
                datasets: [{
                    data: [
                        {{ $loans->where('status', 'disbursed')->count() }},
                        {{ $loans->where('status', 'pending')->count() }},
                        {{ $loans->where('status', 'completed')->count() }}
                    ],
                    backgroundColor: ['#1cc88a', '#f6c23e', '#4e73df']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'My Loan Status'
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    // Balance Chart
    const balanceCtx = document.getElementById('balanceChart');
    if (balanceCtx) {
        new Chart(balanceCtx, {
            type: 'bar',
            data: {
                labels: ['Outstanding Loans', 'Savings Balance'],
                datasets: [{
                    label: 'Amount ($)',
                    data: [
                        {{ $loans->where('status', 'disbursed')->sum('outstanding_balance') }},
                        {{ $savingsAccounts->sum('balance') }}
                    ],
                    backgroundColor: ['#f6c23e', '#1cc88a']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'My Financial Balance'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}
</script>
@endsection
