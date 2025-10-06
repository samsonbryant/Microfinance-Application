@extends('layouts.app')

@section('title', 'Portfolio at Risk Report')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Portfolio at Risk Report</h1>
    <p class="page-subtitle">Analysis of loans at risk of default.</p>
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
                            <a href="{{ route('reports.portfolio-at-risk') }}" class="btn btn-outline-secondary">
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
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-value">{{ $data['total_loans'] ?? 0 }}</div>
            <div class="stat-label">Total Loans</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="stat-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-value">{{ $data['par_loans'] ?? 0 }}</div>
            <div class="stat-label">PAR Loans</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="stat-icon">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-value">{{ number_format($data['par_percentage'] ?? 0, 1) }}%</div>
            <div class="stat-label">PAR Percentage</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-value">${{ number_format(($data['par_amount'] ?? 0) / 1000, 1) }}K</div>
            <div class="stat-label">PAR Amount</div>
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

<!-- PAR Loans Table -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Portfolio at Risk Details</h6>
    </div>
    <div class="card-body">
        @if(isset($data['loans']) && count($data['loans']) > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Loan Number</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Outstanding</th>
                            <th>Days Overdue</th>
                            <th>Risk Level</th>
                            <th>Branch</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['loans'] as $loan)
                        <tr>
                            <td><strong>{{ $loan['loan_number'] }}</strong></td>
                            <td>
                                <div>
                                    <strong>{{ $loan['client_name'] }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $loan['client_number'] }}</small>
                                </div>
                            </td>
                            <td>${{ number_format($loan['amount'], 2) }}</td>
                            <td>${{ number_format($loan['outstanding_balance'], 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $loan['days_overdue'] > 90 ? 'danger' : ($loan['days_overdue'] > 30 ? 'warning' : 'info') }}">
                                    {{ $loan['days_overdue'] }} days
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $loan['risk_level'] === 'high' ? 'danger' : ($loan['risk_level'] === 'medium' ? 'warning' : 'info') }}">
                                    {{ ucfirst($loan['risk_level']) }}
                                </span>
                            </td>
                            <td>{{ $loan['branch_name'] }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('loans.show', $loan['id']) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-warning" title="Contact Client">
                                        <i class="fas fa-phone"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" title="Recovery Action">
                                        <i class="fas fa-gavel"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Portfolio at Risk Data</h5>
                <p class="text-muted">No loans are currently at risk for the selected period.</p>
            </div>
        @endif
    </div>
</div>
@endsection
