@extends('layouts.app')

@section('title', 'My Financial Report')

@section('content')
<div class="container-fluid" style="font-family: 'Inter', sans-serif;">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-chart-line text-primary"></i> My Financial Report
            </h2>
            <p class="text-muted mb-0">Overview of your loans, savings, and payment history</p>
        </div>
        <a href="{{ route('borrower.reports.financial', ['export' => 'pdf', 'from_date' => $fromDate, 'to_date' => $toDate]) }}" 
           class="btn btn-danger" style="border-radius: 8px;">
            <i class="fas fa-file-pdf"></i> Download PDF
        </a>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <div class="card-body">
            <form method="GET" action="{{ route('borrower.reports.financial') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}" style="border-radius: 8px;">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $toDate }}" style="border-radius: 8px;">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100" style="border-radius: 8px;">
                            <i class="fas fa-sync"></i> Generate Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Borrowed -->
        <div class="col-md-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <h6 class="mb-2 opacity-75">Total Borrowed</h6>
                    <h2 class="mb-0 fw-bold">${{ number_format($loanStats['total_borrowed'], 2) }}</h2>
                    <small class="opacity-75">{{ $loanStats['active_loans_count'] }} active loans</small>
                </div>
            </div>
        </div>

        <!-- Outstanding Balance -->
        <div class="col-md-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <h6 class="mb-2 opacity-75">Outstanding</h6>
                    <h2 class="mb-0 fw-bold">${{ number_format($loanStats['outstanding_balance'], 2) }}</h2>
                    <small class="opacity-75">Amount to repay</small>
                </div>
            </div>
        </div>

        <!-- Total Paid -->
        <div class="col-md-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <h6 class="mb-2 opacity-75">Total Paid</h6>
                    <h2 class="mb-0 fw-bold">${{ number_format($loanStats['total_paid'], 2) }}</h2>
                    <small class="opacity-75">All-time payments</small>
                </div>
            </div>
        </div>

        <!-- Savings Balance -->
        <div class="col-md-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #14B8A6 0%, #0D9488 100%); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <h6 class="mb-2 opacity-75">Savings Balance</h6>
                    <h2 class="mb-0 fw-bold">${{ number_format($savingsStats['total_savings'], 2) }}</h2>
                    <small class="opacity-75">{{ $savingsStats['active_accounts'] }} accounts</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Breakdown for Period -->
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-chart-pie text-primary"></i> Payment Breakdown
                        <small class="text-muted">({{ date('M d', strtotime($fromDate)) }} - {{ date('M d, Y', strtotime($toDate)) }})</small>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="paymentBreakdownChart" height="200"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="fas fa-circle text-primary"></i> Principal Paid</span>
                                    <strong>${{ number_format($paymentStats['principal_paid'], 2) }}</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $paymentStats['total_payments'] > 0 ? ($paymentStats['principal_paid'] / $paymentStats['total_payments'] * 100) : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="fas fa-circle text-success"></i> Interest Paid</span>
                                    <strong>${{ number_format($paymentStats['interest_paid'], 2) }}</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: {{ $paymentStats['total_payments'] > 0 ? ($paymentStats['interest_paid'] / $paymentStats['total_payments'] * 100) : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="fas fa-circle text-warning"></i> Penalties Paid</span>
                                    <strong>${{ number_format($paymentStats['penalty_paid'], 2) }}</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $paymentStats['total_payments'] > 0 ? ($paymentStats['penalty_paid'] / $paymentStats['total_payments'] * 100) : 0 }}%"></div>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Total Payments</strong>
                                <h5 class="mb-0 text-success">${{ number_format($paymentStats['total_payments'], 2) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Credit Score Card -->
        <div class="col-md-4">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-star text-warning"></i> Credit Score
                    </h5>
                </div>
                <div class="card-body text-center">
                    <h1 class="display-3 fw-bold text-{{ $creditStatus['class'] }}">{{ $creditScore }}</h1>
                    <span class="badge bg-{{ $creditStatus['class'] }} mb-3">{{ $creditStatus['label'] }}</span>
                    <p class="text-muted small">
                        A good credit score helps you get better loan terms and higher approval rates.
                    </p>
                    <div class="progress" style="height: 12px;">
                        <div class="progress-bar bg-{{ $creditStatus['class'] }}" style="width: {{ ($creditScore / 850) * 100 }}%"></div>
                    </div>
                    <small class="text-muted d-block mt-2">Max: 850</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 12-Month Trends -->
    <div class="card mb-4" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <div class="card-header bg-white border-0 pt-4">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-chart-area text-success"></i> 12-Month Payment Trends
            </h5>
        </div>
        <div class="card-body">
            <canvas id="trendsChart" height="80"></canvas>
        </div>
    </div>

    <!-- Upcoming Payments -->
    <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <div class="card-header bg-white border-0 pt-4">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-calendar-alt text-warning"></i> Upcoming Payments (Next 30 Days)
            </h5>
        </div>
        <div class="card-body">
            @if($upcomingPayments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Loan</th>
                                <th>Amount Due</th>
                                <th>Due Date</th>
                                <th>Days Until Due</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingPayments as $loan)
                            <tr>
                                <td>Loan #{{ $loan->loan_number }}</td>
                                <td class="fw-bold">${{ number_format($loan->next_payment_amount ?? 0, 2) }}</td>
                                <td>{{ $loan->next_due_date->format('M d, Y') }}</td>
                                <td>
                                    @php
                                        $daysUntil = now()->diffInDays($loan->next_due_date, false);
                                    @endphp
                                    <span class="badge bg-{{ $daysUntil <= 7 ? 'danger' : ($daysUntil <= 14 ? 'warning' : 'info') }}">
                                        {{ $daysUntil }} days
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('borrower.payments.create', ['loan_id' => $loan->id]) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-credit-card"></i> Pay Now
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5 class="text-muted">No Payments Due</h5>
                    <p class="text-muted">You're all caught up! No payments due in the next 30 days.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Payment Breakdown Pie Chart
const pieCtx = document.getElementById('paymentBreakdownChart');
new Chart(pieCtx, {
    type: 'doughnut',
    data: {
        labels: ['Principal', 'Interest', 'Penalties'],
        datasets: [{
            data: [
                {{ $paymentStats['principal_paid'] }},
                {{ $paymentStats['interest_paid'] }},
                {{ $paymentStats['penalty_paid'] }}
            ],
            backgroundColor: ['#3B82F6', '#10B981', '#F59E0B'],
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

// 12-Month Trends Line Chart
const trendsCtx = document.getElementById('trendsChart');
new Chart(trendsCtx, {
    type: 'line',
    data: {
        labels: @json(array_column($trends, 'month')),
        datasets: [
            {
                label: 'Total Payments',
                data: @json(array_column($trends, 'payments')),
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Principal',
                data: @json(array_column($trends, 'principal')),
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Interest',
                data: @json(array_column($trends, 'interest')),
                borderColor: '#F59E0B',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                mode: 'index',
                intersect: false,
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
@endpush
@endsection

