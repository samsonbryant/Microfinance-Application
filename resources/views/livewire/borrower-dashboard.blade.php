<div wire:poll.{{ $refreshInterval }}ms="refreshData" class="borrower-dashboard" style="font-family: 'Inter', sans-serif;">
    <!-- Real-time Update Indicator (inside main container) -->
    <div wire:loading class="alert alert-info d-flex align-items-center mb-3" style="border-radius: 8px;">
        <i class="fas fa-sync fa-spin me-2"></i>
        Updating dashboard...
    </div>

    <!-- Summary Cards with Lendbox Styling -->
    <div class="row g-4 mb-4">
        <!-- Active Loans Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card text-white" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75 small">Active Loans</p>
                            <h2 class="mb-0 fw-bold">{{ $stats['active_loans'] }}</h2>
                        </div>
                        <div class="icon-box" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-hand-holding-usd fa-2x"></i>
                        </div>
                    </div>
                    <small class="opacity-75">
                        <i class="fas fa-check-circle"></i> {{ $stats['total_loans'] }} total
                    </small>
                </div>
            </div>
        </div>

        <!-- Outstanding Balance Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card text-white" style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75 small">Outstanding</p>
                            <h2 class="mb-0 fw-bold">${{ number_format($stats['outstanding_balance'], 2) }}</h2>
                        </div>
                        <div class="icon-box" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                    <small class="opacity-75">
                        <i class="fas fa-wallet"></i> Pay soon
                    </small>
                </div>
            </div>
        </div>

        <!-- Savings Balance Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card text-white" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75 small">Savings Balance</p>
                            <h2 class="mb-0 fw-bold">${{ number_format($stats['savings_balance'], 2) }}</h2>
                        </div>
                        <div class="icon-box" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-piggy-bank fa-2x"></i>
                        </div>
                    </div>
                    <small class="opacity-75">
                        <i class="fas fa-chart-line"></i> {{ $stats['savings_accounts'] }} account{{ $stats['savings_accounts'] != 1 ? 's' : '' }}
                    </small>
                </div>
            </div>
        </div>

        <!-- Next Payment Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card text-white" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75 small">Next Payment</p>
                            <h2 class="mb-0 fw-bold">${{ number_format($stats['next_payment_amount'], 2) }}</h2>
                        </div>
                        <div class="icon-box" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                    </div>
                    <small class="opacity-75">
                        @if($stats['next_payment_date'])
                            <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($stats['next_payment_date'])->format('M d, Y') }}
                        @else
                            <i class="fas fa-info-circle"></i> No upcoming payment
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Next Payment Alert -->
    @if($nextPayment)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning d-flex justify-content-between align-items-center" style="border-radius: 12px; border-left: 4px solid #F59E0B;">
                <div>
                    <h5 class="mb-1">
                        <i class="fas fa-exclamation-triangle"></i> Payment Due Soon
                    </h5>
                    <p class="mb-0">
                        <strong>${{ number_format($nextPayment->next_payment_amount, 2) }}</strong> due on 
                        <strong>{{ $nextPayment->next_due_date->format('M d, Y') }}</strong>
                        ({{ $nextPayment->next_due_date->diffForHumans() }})
                    </p>
                </div>
                <a href="{{ route('borrower.payments.create', ['loan_id' => $nextPayment->id]) }}" class="btn btn-warning">
                    <i class="fas fa-credit-card"></i> Pay Now
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <h6 class="mb-3 fw-semibold">Quick Actions</h6>
                    <div class="row g-3">
                        <div class="col-md-2">
                            <a href="{{ route('borrower.payments.create') }}" class="btn btn-success w-100" style="border-radius: 8px;">
                                <i class="fas fa-credit-card d-block mb-2" style="font-size: 24px;"></i>
                                <small>Make Payment</small>
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('borrower.loans.create') }}" class="btn btn-primary w-100" style="border-radius: 8px;">
                                <i class="fas fa-plus-circle d-block mb-2" style="font-size: 24px;"></i>
                                <small>Apply for Loan</small>
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('borrower.loans.index') }}" class="btn btn-info w-100" style="border-radius: 8px;">
                                <i class="fas fa-list d-block mb-2" style="font-size: 24px;"></i>
                                <small>My Loans</small>
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('borrower.savings.index') }}" class="btn btn-teal w-100" style="border-radius: 8px; background: #14B8A6; color: white;">
                                <i class="fas fa-piggy-bank d-block mb-2" style="font-size: 24px;"></i>
                                <small>My Savings</small>
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('borrower.transactions.index') }}" class="btn btn-secondary w-100" style="border-radius: 8px;">
                                <i class="fas fa-history d-block mb-2" style="font-size: 24px;"></i>
                                <small>Transactions</small>
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('borrower.profile') }}" class="btn btn-outline-primary w-100" style="border-radius: 8px;">
                                <i class="fas fa-user d-block mb-2" style="font-size: 24px;"></i>
                                <small>My Profile</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- My Loans & Recent Transactions -->
    <div class="row g-4">
        <!-- My Loans -->
        <div class="col-lg-6">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-hand-holding-usd text-primary"></i> My Loans
                    </h5>
                </div>
                <div class="card-body">
                    @if($loans->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Amount</th>
                                        <th>Outstanding</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loans->take(5) as $loan)
                                    <tr>
                                        <td class="fw-bold">${{ number_format($loan->amount, 2) }}</td>
                                        <td>${{ number_format($loan->outstanding_balance, 2) }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'disbursed' => 'success',
                                                    'active' => 'success',
                                                    'pending' => 'warning',
                                                    'approved' => 'info',
                                                    'overdue' => 'danger',
                                                    'closed' => 'secondary'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$loan->status] ?? 'secondary' }}">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('borrower.loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('borrower.loans.index') }}" class="btn btn-primary" style="border-radius: 8px;">
                                View All Loans
                            </a>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-hand-holding-usd fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No loans yet</h5>
                            <p class="text-muted">Apply for your first loan to get started.</p>
                            <a href="{{ route('borrower.loans.create') }}" class="btn btn-primary" style="border-radius: 8px;">
                                <i class="fas fa-plus"></i> Apply for Loan
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="col-lg-6">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-history text-success"></i> Recent Transactions
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentTransactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
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
                                            @php
                                                $typeColors = [
                                                    'deposit' => 'success',
                                                    'withdrawal' => 'warning',
                                                    'loan_payment' => 'primary',
                                                    'loan_repayment' => 'primary',
                                                    'repayment' => 'primary'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $typeColors[$transaction->type] ?? 'info' }}">
                                                {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                            </span>
                                        </td>
                                        <td class="fw-bold">${{ number_format($transaction->amount, 2) }}</td>
                                        <td class="small">{{ $transaction->created_at->format('M d, Y') }}</td>
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
                            <a href="{{ route('borrower.transactions.index') }}" class="btn btn-success" style="border-radius: 8px;">
                                View All Transactions
                            </a>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No transactions yet</h5>
                            <p class="text-muted">Your transaction history will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-refresh indicator -->
    <div class="text-center mt-3">
        <small class="text-muted">
            <i class="fas fa-sync-alt"></i> Dashboard auto-refreshes every 30 seconds
        </small>
    </div>

    <!-- Inline styles (must be inside root element for Livewire) -->
    <style>
        .borrower-dashboard {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .borrower-dashboard .card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .borrower-dashboard .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 12px -2px rgba(0,0,0,0.15) !important;
        }

        .borrower-dashboard .btn {
            transition: all 0.2s;
        }

        .borrower-dashboard .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
    </style>
</div>

