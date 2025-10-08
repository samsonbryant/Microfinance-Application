@extends('layouts.app')

@section('title', 'Staff Report')

@section('content')
<div class="page-header">
    <h1 class="page-title">Staff Report</h1>
    <p class="page-subtitle">Staff performance and statistics</p>
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
                <form method="GET" action="{{ route('reports.staff') }}" class="row g-3">
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
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('reports.staff') }}" class="btn btn-secondary">Reset</a>
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
                <h6>Total Staff</h6>
                <h2>{{ $staffStats['total_staff'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white shadow">
            <div class="card-body text-center">
                <h6>Loan Officers</h6>
                <h2>{{ $staffStats['loan_officers'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white shadow">
            <div class="card-body text-center">
                <h6>Branch Managers</h6>
                <h2>{{ $staffStats['branch_managers'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white shadow">
            <div class="card-body text-center">
                <h6>Total Loans Managed</h6>
                <h2>{{ $staffStats['total_loans_managed'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Staff List -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users me-2"></i>Staff Performance
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
                                <th>Name</th>
                                <th>Role</th>
                                <th>Branch</th>
                                <th>Loans Managed</th>
                                <th>Clients Managed</th>
                                <th>Status</th>
                                <th>Joined Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staff as $member)
                                <tr>
                                    <td>{{ $member->name }}</td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ $member->getRoleNames()->first() ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>{{ $member->branch->name ?? 'N/A' }}</td>
                                    <td>{{ $member->loans_count ?? 0 }}</td>
                                    <td>{{ $member->clients_count ?? 0 }}</td>
                                    <td>
                                        <span class="badge bg-{{ $member->is_active ? 'success' : 'secondary' }}">
                                            {{ $member->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $member->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No staff members found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $staff->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

