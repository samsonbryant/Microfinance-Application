@extends('layouts.app')

@section('title', 'Collateral Management')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-shield-alt text-primary me-2"></i>Collateral Management
            </h1>
            <p class="text-muted mb-0">Manage and track loan collaterals</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('collaterals.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Add Collateral
            </a>
            <button type="button" class="btn btn-info" onclick="refreshCollaterals()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters and Search -->
    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-2"></i>Search & Filter
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('collaterals.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Search collateral...">
                    </div>
                    <div class="col-md-2">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="property" {{ request('type') === 'property' ? 'selected' : '' }}>Property</option>
                            <option value="vehicle" {{ request('type') === 'vehicle' ? 'selected' : '' }}>Vehicle</option>
                            <option value="equipment" {{ request('type') === 'equipment' ? 'selected' : '' }}>Equipment</option>
                            <option value="jewelry" {{ request('type') === 'jewelry' ? 'selected' : '' }}>Jewelry</option>
                            <option value="electronics" {{ request('type') === 'electronics' ? 'selected' : '' }}>Electronics</option>
                            <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="released" {{ request('status') === 'released' ? 'selected' : '' }}>Released</option>
                            <option value="seized" {{ request('status') === 'seized' ? 'selected' : '' }}>Seized</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="value_range" class="form-label">Value Range</label>
                        <select class="form-select" id="value_range" name="value_range">
                            <option value="">All Values</option>
                            <option value="0-1000" {{ request('value_range') === '0-1000' ? 'selected' : '' }}>$0 - $1,000</option>
                            <option value="1000-5000" {{ request('value_range') === '1000-5000' ? 'selected' : '' }}>$1,000 - $5,000</option>
                            <option value="5000-10000" {{ request('value_range') === '5000-10000' ? 'selected' : '' }}>$5,000 - $10,000</option>
                            <option value="10000-50000" {{ request('value_range') === '10000-50000' ? 'selected' : '' }}>$10,000 - $50,000</option>
                            <option value="50000+" {{ request('value_range') === '50000+' ? 'selected' : '' }}>$50,000+</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="{{ route('collaterals.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Collaterals
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $collaterals->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shield-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Value
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($collaterals->sum('value') ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Collaterals
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $collaterals->where('status', 'active')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Review
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $collaterals->where('status', 'pending')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Collaterals Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>Collateral Inventory
            </h6>
        </div>
        <div class="card-body">
            @if($collaterals->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="collateralsTable">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Value</th>
                                <th>Status</th>
                                <th>Loan</th>
                                <th>Date Added</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($collaterals as $collateral)
                            <tr>
                                <td>
                                    <span class="badge bg-primary">#{{ $collateral->id }}</span>
                                </td>
                                <td>
                                    @if($collateral->client)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                @if($collateral->client->avatar)
                                                    <img class="img-profile rounded-circle" width="30" height="30" 
                                                         src="{{ asset('storage/' . $collateral->client->avatar) }}" 
                                                         alt="{{ $collateral->client->full_name }}">
                                                @else
                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width: 30px; height: 30px;">
                                                        <span class="text-white font-weight-bold">
                                                            {{ substr($collateral->client->first_name, 0, 1) }}{{ substr($collateral->client->last_name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">{{ $collateral->client->full_name }}</div>
                                                <small class="text-muted">{{ $collateral->client->phone }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">No client</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($collateral->type) }}</span>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $collateral->description }}">
                                        {{ $collateral->description }}
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-success">${{ number_format($collateral->value ?? 0, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $collateral->getStatusBadgeClass() }}">
                                        {{ ucfirst($collateral->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($collateral->loans && $collateral->loans->count() > 0)
                                        @foreach($collateral->loans->take(2) as $loan)
                                            <a href="{{ route('loans.show', $loan->id) }}" class="badge bg-secondary text-decoration-none">
                                                {{ $loan->loan_number }}
                                            </a>
                                        @endforeach
                                        @if($collateral->loans->count() > 2)
                                            <span class="badge bg-light text-dark">+{{ $collateral->loans->count() - 2 }} more</span>
                                        @endif
                                    @else
                                        <span class="text-muted">No loans</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $collateral->created_at->format('M d, Y') }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('collaterals.show', $collateral->id) }}" 
                                           class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('collaterals.edit', $collateral->id) }}" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($collateral->status === 'pending')
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="verifyCollateral({{ $collateral->id }})" title="Verify">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteCollateral({{ $collateral->id }})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <p class="text-muted mb-0">
                            Showing {{ $collaterals->firstItem() ?? 0 }} to {{ $collaterals->lastItem() ?? 0 }} 
                            of {{ $collaterals->total() }} results
                        </p>
                    </div>
                    <div>
                        {{ $collaterals->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-shield-alt fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No collaterals found</h5>
                    <p class="text-muted">No collaterals match your current filters.</p>
                    <a href="{{ route('collaterals.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add First Collateral
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function refreshCollaterals() {
    location.reload();
}

function verifyCollateral(collateralId) {
    if (confirm('Are you sure you want to verify this collateral?')) {
        fetch(`/collaterals/${collateralId}/verify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error verifying collateral: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Network error occurred');
        });
    }
}

function deleteCollateral(collateralId) {
    if (confirm('Are you sure you want to delete this collateral? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/collaterals/${collateralId}`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Initialize DataTable if available
$(document).ready(function() {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#collateralsTable').DataTable({
            "paging": false,
            "searching": false,
            "info": false,
            "order": [[ 7, "desc" ]],
            "columnDefs": [
                { "orderable": false, "targets": [8] }
            ]
        });
    }
});
</script>

<style>
.border-left-primary {
    border-left: 4px solid #4e73df !important;
}

.border-left-success {
    border-left: 4px solid #1cc88a !important;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.avatar-sm img {
    object-fit: cover;
}

.text-truncate {
    max-width: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
@endsection