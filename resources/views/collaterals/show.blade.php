@extends('layouts.app')

@section('title', 'Collateral Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-shield-alt text-primary me-2"></i>Collateral Details
            </h1>
            <p class="text-muted mb-0">ID: #{{ $collateral->id }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('collaterals.edit', $collateral) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            <a href="{{ route('collaterals.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Collateral Information -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Collateral Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Type:</th>
                            <td><span class="badge bg-info">{{ ucfirst($collateral->type) }}</span></td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>{{ $collateral->description }}</td>
                        </tr>
                        <tr>
                            <th>Estimated Value:</th>
                            <td><strong class="text-success">${{ number_format($collateral->value ?? 0, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Location:</th>
                            <td>{{ $collateral->location ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Condition:</th>
                            <td>{{ ucfirst($collateral->condition ?? 'N/A') }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td><span class="badge bg-{{ $collateral->getStatusBadgeClass() }}">{{ ucfirst($collateral->status) }}</span></td>
                        </tr>
                        <tr>
                            <th>Notes:</th>
                            <td>{{ $collateral->notes ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Associated Loans -->
            @if($collateral->loans && $collateral->loans->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Associated Loans</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Loan Number</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($collateral->loans as $loan)
                                <tr>
                                    <td>{{ $loan->loan_number }}</td>
                                    <td>${{ number_format($loan->amount, 2) }}</td>
                                    <td><span class="badge bg-primary">{{ ucfirst($loan->status) }}</span></td>
                                    <td>{{ $loan->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Client Information -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Client Information</h6>
                </div>
                <div class="card-body">
                    @if($collateral->client)
                        <p><strong>Name:</strong><br>{{ $collateral->client->full_name }}</p>
                        <p><strong>Client Number:</strong><br>{{ $collateral->client->client_number }}</p>
                        <p><strong>Phone:</strong><br>{{ $collateral->client->phone }}</p>
                        <p><strong>Email:</strong><br>{{ $collateral->client->email ?? 'N/A' }}</p>
                        <a href="{{ route('clients.show', $collateral->client) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-user me-1"></i>View Client
                        </a>
                    @else
                        <p class="text-muted">No client assigned</p>
                    @endif
                </div>
            </div>

            <!-- Timestamps -->
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="m-0 font-weight-bold">Record Information</h6>
                </div>
                <div class="card-body">
                    <p><strong>Created:</strong><br>{{ $collateral->created_at->format('M d, Y H:i') }}</p>
                    <p><strong>Last Updated:</strong><br>{{ $collateral->updated_at->format('M d, Y H:i') }}</p>
                    @if($collateral->valued_by)
                        <p><strong>Valued By:</strong><br>{{ $collateral->valuedBy->name ?? 'N/A' }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

