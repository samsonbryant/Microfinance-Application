@extends('layouts.app')

@section('content')
<div class="container-fluid" style="font-family: 'Inter', sans-serif;">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-chart-line text-success"></i> Profit & Loss Statement
            </h2>
            <p class="text-muted mb-0">Income statement for selected period</p>
        </div>
        <div class="btn-group" style="border-radius: 8px;">
            <a href="{{ route('accounting.reports.profit-loss.export', 'pdf') }}?from_date={{ request('from_date', now()->startOfMonth()->toDateString()) }}&to_date={{ request('to_date', now()->toDateString()) }}" 
               class="btn btn-outline-danger" style="border-radius: 8px 0 0 8px;">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <a href="{{ route('accounting.reports.profit-loss.export', 'excel') }}?from_date={{ request('from_date', now()->startOfMonth()->toDateString()) }}&to_date={{ request('to_date', now()->toDateString()) }}" 
               class="btn btn-outline-success">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('accounting.reports.profit-loss.export', 'csv') }}?from_date={{ request('from_date', now()->startOfMonth()->toDateString()) }}&to_date={{ request('to_date', now()->toDateString()) }}" 
               class="btn btn-outline-info" style="border-radius: 0 8px 8px 0;">
                <i class="fas fa-file-csv"></i> CSV
            </a>
        </div>
    </div>

    <!-- Date Filter Card -->
    <div class="card mb-4" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <div class="card-body">
            <form method="GET" action="{{ route('accounting.reports.profit-loss') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date', now()->startOfMonth()->toDateString()) }}" style="border-radius: 8px;">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date', now()->toDateString()) }}" style="border-radius: 8px;">
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

    <div class="row g-4">
        <!-- Summary Cards -->
        <div class="col-md-4">
            <div class="card text-white" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <h6 class="mb-2 opacity-75">Total Revenue</h6>
                    <h2 class="mb-0 fw-bold">${{ number_format($data['total_revenue'], 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white" style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <h6 class="mb-2 opacity-75">Total Expenses</h6>
                    <h2 class="mb-0 fw-bold">${{ number_format($data['total_expenses'], 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white" style="background: linear-gradient(135deg, #14B8A6 0%, #0D9488 100%); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <h6 class="mb-2 opacity-75">Net Income</h6>
                    <h2 class="mb-0 fw-bold">${{ number_format($data['net_income'], 2) }}</h2>
                </div>
            </div>
        </div>

        <!-- Revenue Section -->
        <div class="col-md-6">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0 fw-semibold text-success">
                        <i class="fas fa-arrow-up"></i> Revenue
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Account</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['revenues'] as $revenue)
                            <tr>
                                <td>{{ $revenue['account']->name }}</td>
                                <td class="text-end fw-bold text-success">${{ number_format($revenue['balance'], 2) }}</td>
                            </tr>
                            @endforeach
                            <tr class="table-success fw-bold">
                                <td>Total Revenue</td>
                                <td class="text-end">${{ number_format($data['total_revenue'], 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Expenses Section -->
        <div class="col-md-6">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0 fw-semibold text-danger">
                        <i class="fas fa-arrow-down"></i> Expenses
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Account</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['expenses'] as $expense)
                            <tr>
                                <td>{{ $expense['account']->name }}</td>
                                <td class="text-end fw-bold text-danger">${{ number_format($expense['balance'], 2) }}</td>
                            </tr>
                            @endforeach
                            <tr class="table-danger fw-bold">
                                <td>Total Expenses</td>
                                <td class="text-end">${{ number_format($data['total_expenses'], 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Monthly Trends Chart -->
        <div class="col-md-12">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-chart-area text-primary"></i> 12-Month Trends
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="trendsChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('trendsChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json(array_column($trends, 'month')),
        datasets: [
            {
                label: 'Revenue',
                data: @json(array_column($trends, 'revenue')),
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4
            },
            {
                label: 'Expenses',
                data: @json(array_column($trends, 'expenses')),
                borderColor: '#EF4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4
            },
            {
                label: 'Net Income',
                data: @json(array_column($trends, 'net_income')),
                borderColor: '#14B8A6',
                backgroundColor: 'rgba(20, 184, 166, 0.1)',
                tension: 0.4
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

