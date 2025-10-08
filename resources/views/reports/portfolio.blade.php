@extends('layouts.app')

@section('title', 'Portfolio Report')

@section('content')
<div class="page-header">
    <h1 class="page-title">Portfolio Report</h1>
    <p class="page-subtitle">Comprehensive loan portfolio analysis</p>
</div>

<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter me-2"></i>Filters
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.portfolio') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" class="form-select">
                            <option value="">All Branches</option>
                            @foreach(\App\Models\Branch::all() as $branch)
                                <option value="{{ $branch->id }}" {{ ($filters['branch_id'] ?? '') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ ($filters['status'] ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="overdue" {{ ($filters['status'] ?? '') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="defaulted" {{ ($filters['status'] ?? '') == 'defaulted' ? 'selected' : '' }}>Defaulted</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('reports.portfolio') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white shadow">
            <div class="card-body text-center">
                <h6>Total Loans</h6>
                <h2>{{ $portfolioStats['total_loans'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white shadow">
            <div class="card-body text-center">
                <h6>Total Amount</h6>
                <h2>${{ number_format($portfolioStats['total_amount'] ?? 0, 0) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white shadow">
            <div class="card-body text-center">
                <h6>Average Loan</h6>
                <h2>${{ number_format($portfolioStats['average_loan_size'] ?? 0, 0) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white shadow">
            <div class="card-body text-center">
                <h6>Active Loans</h6>
                <h2>{{ $portfolioStats['active_loans'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Portfolio Details -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-briefcase me-2"></i>Portfolio Details
                    </h6>
                    <button class="btn btn-sm btn-success" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>Print Report
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Loan #</th>
                                <th>Client</th>
                                <th>Branch</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Outstanding</th>
                                <th>Status</th>
                                <th>Disbursed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loans as $loan)
                                <tr>
                                    <td>{{ $loan->loan_number }}</td>
                                    <td>{{ $loan->client->full_name ?? 'N/A' }}</td>
                                    <td>{{ $loan->branch->name ?? 'N/A' }}</td>
                                    <td>{{ ucfirst($loan->loan_type) }}</td>
                                    <td>${{ number_format($loan->amount, 2) }}</td>
                                    <td>${{ number_format($loan->outstanding_balance, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $loan->status == 'active' ? 'success' : 
                                            ($loan->status == 'overdue' ? 'danger' : 
                                            ($loan->status == 'completed' ? 'info' : 'secondary')) 
                                        }}">
                                            {{ ucfirst($loan->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $loan->disbursement_date ? $loan->disbursement_date->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No loans found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $loans->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
