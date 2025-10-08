@extends('layouts.app')

@section('title', 'Profit & Loss Statement')

@section('content')
<div class="page-header">
    <h1 class="page-title">Profit & Loss Statement</h1>
    <p class="page-subtitle">Income and expenses summary</p>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>
                        P&L for {{ $startDate->format('M d, Y') }} to {{ $endDate->format('M d, Y') }}
                    </h6>
                    <div>
                        <a href="{{ route('general-ledger.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                        <button class="btn btn-sm btn-primary" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Print
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="mb-0">Total Revenue</h5>
                                <h2 class="mb-0">${{ number_format($profitLoss['revenue'] ?? 0, 2) }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="mb-0">Total Expenses</h5>
                                <h2 class="mb-0">${{ number_format($profitLoss['expenses'] ?? 0, 2) }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card {{ ($profitLoss['net_profit'] ?? 0) >= 0 ? 'bg-success' : 'bg-danger' }} text-white">
                    <div class="card-body text-center">
                        <h4 class="mb-0">Net {{ ($profitLoss['net_profit'] ?? 0) >= 0 ? 'Profit' : 'Loss' }}</h4>
                        <h1 class="mb-0 fw-bold">${{ number_format(abs($profitLoss['net_profit'] ?? 0), 2) }}</h1>
                    </div>
                </div>

                <div class="mt-4">
                    <h5>Revenue Breakdown</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Account</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($profitLoss['revenue_accounts']) && count($profitLoss['revenue_accounts']) > 0)
                                    @foreach($profitLoss['revenue_accounts'] as $account)
                                        <tr>
                                            <td>{{ $account['name'] }}</td>
                                            <td class="text-end">${{ number_format($account['amount'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No revenue accounts</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    <h5>Expense Breakdown</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Account</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($profitLoss['expense_accounts']) && count($profitLoss['expense_accounts']) > 0)
                                    @foreach($profitLoss['expense_accounts'] as $account)
                                        <tr>
                                            <td>{{ $account['name'] }}</td>
                                            <td class="text-end">${{ number_format($account['amount'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No expense accounts</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

