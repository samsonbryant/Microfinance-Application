@extends('layouts.app')

@section('title', 'Loan Applications')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Loan Applications</h1>
            <p class="page-subtitle">Manage and review loan applications</p>
        </div>
        <a href="{{ route('loan-applications.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Application
        </a>
    </div>
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
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('loan-applications.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Applications List -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-file-alt me-2"></i>Loan Applications
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Application #</th>
                                <th>Client</th>
                                <th>Branch</th>
                                <th>Loan Type</th>
                                <th>Amount</th>
                                <th>Term</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $application)
                                <tr>
                                    <td>
                                        <strong>{{ $application->application_number }}</strong>
                                    </td>
                                    <td>{{ $application->client->full_name ?? 'N/A' }}</td>
                                    <td>{{ $application->branch->name ?? 'N/A' }}</td>
                                    <td>{{ ucfirst($application->loan_type) }}</td>
                                    <td>${{ number_format($application->requested_amount, 2) }}</td>
                                    <td>{{ $application->term_months }} months</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $application->status == 'pending' ? 'warning' : 
                                            ($application->status == 'approved' ? 'success' : 
                                            ($application->status == 'rejected' ? 'danger' : 'secondary')) 
                                        }}">
                                            {{ ucfirst($application->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $application->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('loan-applications.show', $application) }}" class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($application->status == 'pending')
                                            <a href="{{ route('loan-applications.edit', $application) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No loan applications found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $applications->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

