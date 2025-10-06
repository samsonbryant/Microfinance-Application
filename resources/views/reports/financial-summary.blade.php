@extends('layouts.app')

@section('title', 'Financial Summary Report')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Financial Summary Report</h1>
    <p class="page-subtitle">Comprehensive financial overview and analysis.</p>
</div>

<!-- Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ request('start_date', now()->subYear()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="{{ request('end_date', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="branch_id" class="form-label">Branch</label>
                        <select class="form-select" id="branch_id" name="branch_id">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('reports.financial-summary') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-refresh"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-value">${{ number_format(($data['total_revenue'] ?? 0) / 1000, 1) }}K</div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-value">${{ number_format(($data['net_profit'] ?? 0) / 1000, 1) }}K</div>
            <div class="stat-label">Net Profit</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="stat-icon">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-value">{{ number_format($data['profit_margin'] ?? 0, 1) }}%</div>
            <div class="stat-label">Profit Margin</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="stat-icon">
                <i class="fas fa-trending-up"></i>
            </div>
            <div class="stat-value">{{ number_format($data['roi'] ?? 0, 1) }}%</div>
            <div class="stat-label">ROI</div>
        </div>
    </div>
</div>

<!-- Export Options -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
        </div>
    </div>
</div>

<!-- Financial Details -->
<div class="row">
    <!-- Income Statement -->
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Income Statement</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td><strong>Interest Income</strong></td>
                                <td class="text-end">${{ number_format($data['interest_income'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Fee Income</strong></td>
                                <td class="text-end">${{ number_format($data['fee_income'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Other Income</strong></td>
                                <td class="text-end">${{ number_format($data['other_income'] ?? 0, 2) }}</td>
                            </tr>
                            <tr class="table-success">
                                <td><strong>Total Revenue</strong></td>
                                <td class="text-end"><strong>${{ number_format($data['total_revenue'] ?? 0, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr></td>
                            </tr>
                            <tr>
                                <td><strong>Operating Expenses</strong></td>
                                <td class="text-end">${{ number_format($data['operating_expenses'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Interest Expenses</strong></td>
                                <td class="text-end">${{ number_format($data['interest_expenses'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Provision for Bad Debts</strong></td>
                                <td class="text-end">${{ number_format($data['provision_bad_debts'] ?? 0, 2) }}</td>
                            </tr>
                            <tr class="table-danger">
                                <td><strong>Total Expenses</strong></td>
                                <td class="text-end"><strong>${{ number_format($data['total_expenses'] ?? 0, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr></td>
                            </tr>
                            <tr class="table-primary">
                                <td><strong>Net Profit</strong></td>
                                <td class="text-end"><strong>${{ number_format($data['net_profit'] ?? 0, 2) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Sheet -->
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Balance Sheet</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td colspan="2"><strong>ASSETS</strong></td>
                            </tr>
                            <tr>
                                <td>&nbsp;&nbsp;Cash and Cash Equivalents</td>
                                <td class="text-end">${{ number_format($data['cash_equivalents'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td>&nbsp;&nbsp;Loans Outstanding</td>
                                <td class="text-end">${{ number_format($data['loans_outstanding'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td>&nbsp;&nbsp;Savings Deposits</td>
                                <td class="text-end">${{ number_format($data['savings_deposits'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td>&nbsp;&nbsp;Fixed Assets</td>
                                <td class="text-end">${{ number_format($data['fixed_assets'] ?? 0, 2) }}</td>
                            </tr>
                            <tr class="table-success">
                                <td><strong>Total Assets</strong></td>
                                <td class="text-end"><strong>${{ number_format($data['total_assets'] ?? 0, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>LIABILITIES</strong></td>
                            </tr>
                            <tr>
                                <td>&nbsp;&nbsp;Client Deposits</td>
                                <td class="text-end">${{ number_format($data['client_deposits'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td>&nbsp;&nbsp;Borrowings</td>
                                <td class="text-end">${{ number_format($data['borrowings'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td>&nbsp;&nbsp;Accrued Expenses</td>
                                <td class="text-end">${{ number_format($data['accrued_expenses'] ?? 0, 2) }}</td>
                            </tr>
                            <tr class="table-danger">
                                <td><strong>Total Liabilities</strong></td>
                                <td class="text-end"><strong>${{ number_format($data['total_liabilities'] ?? 0, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr></td>
                            </tr>
                            <tr>
                                <td><strong>Equity</strong></td>
                                <td class="text-end">${{ number_format($data['equity'] ?? 0, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
