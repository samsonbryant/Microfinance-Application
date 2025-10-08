@extends('layouts.app')

@section('title', 'Collections Report')

@section('content')
<div class="page-header">
    <h1 class="page-title">Collections Report</h1>
    <p class="page-subtitle">Payment collections and recovery analysis</p>
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
                <form method="GET" action="{{ route('reports.collections') }}" class="row g-3">
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
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('reports.collections') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-success text-white shadow">
            <div class="card-body text-center">
                <h6>Total Collections</h6>
                <h2>${{ number_format($collectionStats['total_collections'] ?? 0, 0) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white shadow">
            <div class="card-body text-center">
                <h6>Collection Count</h6>
                <h2>{{ $collectionStats['collection_count'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white shadow">
            <div class="card-body text-center">
                <h6>Average Collection</h6>
                <h2>${{ number_format($collectionStats['average_collection'] ?? 0, 0) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white shadow">
            <div class="card-body text-center">
                <h6>Today's Collections</h6>
                <h2>${{ number_format($collectionStats['daily_collections'] ?? 0, 0) }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Collections List -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-money-bill-wave me-2"></i>Collection Details
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
                                <th>Transaction #</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Loan</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($collections as $collection)
                                <tr>
                                    <td>{{ $collection->transaction_number }}</td>
                                    <td>{{ $collection->created_at->format('M d, Y') }}</td>
                                    <td>{{ $collection->client->full_name ?? 'N/A' }}</td>
                                    <td>{{ $collection->loan->loan_number ?? 'N/A' }}</td>
                                    <td>${{ number_format($collection->amount, 2) }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $collection->reference_number ?? 'N/A')) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $collection->status == 'completed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($collection->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No collections found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $collections->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
