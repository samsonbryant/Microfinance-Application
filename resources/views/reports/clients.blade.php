@extends('layouts.app')

@section('title', 'Client Report')

@section('content')
<div class="page-header">
    <h1 class="page-title">Client Report</h1>
    <p class="page-subtitle">Client statistics and analysis</p>
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
                <form method="GET" action="{{ route('reports.clients') }}" class="row g-3">
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
                            <option value="inactive" {{ ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ ($filters['status'] ?? '') == 'suspended' ? 'selected' : '' }}>Suspended</option>
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
                        <a href="{{ route('reports.clients') }}" class="btn btn-secondary">Reset</a>
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
                <h6>Total Clients</h6>
                <h2>{{ $clientStats['total_clients'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white shadow">
            <div class="card-body text-center">
                <h6>Active Clients</h6>
                <h2>{{ $clientStats['active_clients'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white shadow">
            <div class="card-body text-center">
                <h6>New This Month</h6>
                <h2>{{ $clientStats['new_clients'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white shadow">
            <div class="card-body text-center">
                <h6>Avg Loans/Client</h6>
                <h2>{{ number_format($clientStats['average_loan_per_client'] ?? 0, 1) }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Client List -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users me-2"></i>Client Details
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
                                <th>Client ID</th>
                                <th>Name</th>
                                <th>Branch</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Loans</th>
                                <th>Joined Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clients as $client)
                                <tr>
                                    <td>{{ $client->client_number }}</td>
                                    <td>{{ $client->full_name }}</td>
                                    <td>{{ $client->branch->name ?? 'N/A' }}</td>
                                    <td>{{ $client->phone }}</td>
                                    <td>
                                        <span class="badge bg-{{ $client->status == 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($client->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $client->loans->count() }}</td>
                                    <td>{{ $client->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No clients found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $clients->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

