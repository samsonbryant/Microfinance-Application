@extends('layouts.app')

@section('title', 'Branch Details')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Branch Details</h1>
    <p class="page-subtitle">Branch: {{ $branch->name }}</p>
</div>

<div class="row">
    <!-- Branch Information -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Branch Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Branch Name</label>
                            <p class="form-control-plaintext">{{ $branch->name }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Branch Code</label>
                            <p class="form-control-plaintext">{{ $branch->code }}</p>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Address</label>
                    <p class="form-control-plaintext">
                        {{ $branch->address }}<br>
                        {{ $branch->city }}, {{ $branch->state }}<br>
                        {{ $branch->country }}
                    </p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone Number</label>
                            <p class="form-control-plaintext">{{ $branch->phone }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <p class="form-control-plaintext">{{ $branch->email }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Manager Name</label>
                            <p class="form-control-plaintext">{{ $branch->manager_name }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ $branch->is_active ? 'success' : 'danger' }}">
                                    {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('branches.edit', $branch) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Branch
                    </a>
                    <a href="{{ route('branches.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Branch Statistics -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Branch Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h4 class="text-primary">{{ $stats['total_users'] }}</h4>
                        <p class="text-muted mb-0">Users</p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-success">{{ $stats['total_clients'] }}</h4>
                        <p class="text-muted mb-0">Clients</p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-info">{{ $stats['active_loans'] }}</h4>
                        <p class="text-muted mb-0">Active Loans</p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-warning">${{ number_format($stats['total_portfolio'] / 1000, 1) }}K</h4>
                        <p class="text-muted mb-0">Portfolio</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4">
        <!-- Branch Status -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Branch Status</h6>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <div class="status-circle {{ $branch->is_active ? 'active' : 'inactive' }}">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <h5>{{ $branch->is_active ? 'Active' : 'Inactive' }}</h5>
                <p class="text-muted mb-3">{{ $branch->manager_name }}</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('clients.create', ['branch_id' => $branch->id]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus"></i> Add Client
                    </a>
                    <a href="{{ route('loans.create', ['branch_id' => $branch->id]) }}" class="btn btn-outline-success">
                        <i class="fas fa-hand-holding-usd"></i> New Loan
                    </a>
                    <a href="{{ route('savings-accounts.create', ['branch_id' => $branch->id]) }}" class="btn btn-outline-info">
                        <i class="fas fa-piggy-bank"></i> New Savings
                    </a>
                </div>
            </div>
        </div>

        <!-- Branch Details -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Branch Details</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Code:</strong> {{ $branch->code }}
                </div>
                <div class="mb-3">
                    <strong>Created:</strong> {{ $branch->created_at->format('M d, Y') }}
                </div>
                <div class="mb-3">
                    <strong>Total Savings:</strong> ${{ number_format($stats['total_savings'], 2) }}
                </div>
                <div class="mb-3">
                    <strong>Overdue Loans:</strong> {{ $stats['overdue_loans'] ?? 0 }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.status-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto;
}

.status-circle.active {
    background-color: #d4edda;
    color: #155724;
}

.status-circle.inactive {
    background-color: #f8d7da;
    color: #721c24;
}
</style>
@endpush
@endsection
