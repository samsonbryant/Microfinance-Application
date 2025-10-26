@extends('layouts.app')

@section('title', 'Financial Report')

@section('content')
<div class="page-header">
    <h1 class="page-title">Financial Report</h1>
    <p class="page-subtitle">Comprehensive financial overview</p>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter me-2"></i>Filters
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.financial') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('reports.financial') }}" class="btn btn-secondary">
                            <i class="fas fa-redo me-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Financial Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-success text-white shadow">
            <div class="card-body text-center">
                <h5>Total Revenue</h5>
                <h2>${{ number_format($profitLoss['total_revenue'] ?? 0, 2) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white shadow">
            <div class="card-body text-center">
                <h5>Total Expenses</h5>
                <h2>${{ number_format($profitLoss['total_expenses'] ?? 0, 2) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-primary text-white shadow">
            <div class="card-body text-center">
                <h5>Net Income</h5>
                <h2>${{ number_format($profitLoss['net_income'] ?? 0, 2) }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Trial Balance -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-balance-scale me-2"></i>Trial Balance Summary
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th class="text-end">Debits</th>
                                <th class="text-end">Credits</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trialBalance as $account)
                                <tr>
                                    <td>{{ $account['account_code'] }} - {{ $account['account_name'] }}</td>
                                    <td class="text-end">${{ number_format($account['debits'], 2) }}</td>
                                    <td class="text-end">${{ number_format($account['credits'], 2) }}</td>
                                    <td class="text-end">${{ number_format(abs($account['balance']), 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Balance Sheet -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-file-invoice me-2"></i>Balance Sheet Summary
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Assets: ${{ number_format($balanceSheet['total_assets'] ?? 0, 2) }}</h6>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Liabilities: ${{ number_format($balanceSheet['total_liabilities'] ?? 0, 2) }}</h6>
                        <h6 class="fw-bold">Equity: ${{ number_format($balanceSheet['total_equity'] ?? 0, 2) }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

