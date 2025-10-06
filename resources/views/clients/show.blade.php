@extends('layouts.app')

@section('title', 'Client Details')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Client Details</h1>
    <p class="page-subtitle">Client: {{ $client->client_number }}</p>
</div>

<div class="row">
    <!-- Client Information -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Personal Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Client Number</label>
                            <p class="form-control-plaintext">{{ $client->client_number }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Name</label>
                            <p class="form-control-plaintext">{{ $client->full_name }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <p class="form-control-plaintext">{{ $client->email }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone Number</label>
                            <p class="form-control-plaintext">{{ $client->phone }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Date of Birth</label>
                            <p class="form-control-plaintext">{{ $client->date_of_birth->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Gender</label>
                            <p class="form-control-plaintext">{{ ucfirst($client->gender) }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Occupation</label>
                            <p class="form-control-plaintext">{{ $client->occupation }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Monthly Income</label>
                            <p class="form-control-plaintext">${{ number_format($client->monthly_income, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Address</label>
                    <p class="form-control-plaintext">
                        {{ $client->address }}<br>
                        {{ $client->city }}, {{ $client->state }}<br>
                        {{ $client->country }}
                    </p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ $client->status === 'active' ? 'success' : ($client->status === 'suspended' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($client->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">KYC Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ $client->kyc_status === 'verified' ? 'success' : ($client->kyc_status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($client->kyc_status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Branch</label>
                            <p class="form-control-plaintext">{{ $client->branch->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Created Date</label>
                            <p class="form-control-plaintext">{{ $client->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Client
                    </a>
                    @if($client->kyc_status === 'pending')
                        <form action="{{ route('clients.verify-kyc', $client) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Verify KYC
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('clients.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Financial Summary</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h4 class="text-primary">{{ $stats['total_loans'] }}</h4>
                        <p class="text-muted mb-0">Total Loans</p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-success">{{ $stats['active_loans'] }}</h4>
                        <p class="text-muted mb-0">Active Loans</p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-info">${{ number_format($stats['total_borrowed'] / 1000, 1) }}K</h4>
                        <p class="text-muted mb-0">Total Borrowed</p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-warning">${{ number_format($stats['outstanding_balance'] / 1000, 1) }}K</h4>
                        <p class="text-muted mb-0">Outstanding</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6>
            </div>
            <div class="card-body">
                @if($client->transactions && $client->transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($client->transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->type === 'deposit' ? 'success' : 'warning' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td>${{ number_format($transaction->amount, 2) }}</td>
                                    <td>{{ Str::limit($transaction->description, 50) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->status === 'approved' ? 'success' : 'warning' }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-exchange-alt fa-2x text-muted mb-3"></i>
                        <p class="text-muted">No transactions found for this client.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4">
        <!-- Client Status -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Client Status</h6>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <div class="status-circle {{ $client->status === 'active' ? 'active' : 'inactive' }}">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <h5>{{ ucfirst($client->status) }}</h5>
                <p class="text-muted mb-3">{{ ucfirst($client->kyc_status) }} KYC</p>
                
                <div class="d-grid gap-2">
                    @if($client->status === 'active')
                        <form action="{{ route('clients.suspend', $client) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning" onclick="return confirm('Are you sure you want to suspend this client?')">
                                <i class="fas fa-pause"></i> Suspend Client
                            </button>
                        </form>
                    @elseif($client->status === 'suspended')
                        <form action="{{ route('clients.activate', $client) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-success">
                                <i class="fas fa-play"></i> Activate Client
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('loans.create', ['client_id' => $client->id]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-hand-holding-usd"></i> New Loan
                    </a>
                    <a href="{{ route('savings-accounts.create', ['client_id' => $client->id]) }}" class="btn btn-outline-success">
                        <i class="fas fa-piggy-bank"></i> New Savings Account
                    </a>
                    <a href="{{ route('transactions.create', ['client_id' => $client->id]) }}" class="btn btn-outline-info">
                        <i class="fas fa-exchange-alt"></i> New Transaction
                    </a>
                </div>
            </div>
        </div>

        <!-- Client Statistics -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Statistics</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Age:</strong> {{ $client->age }} years
                </div>
                <div class="mb-3">
                    <strong>Member Since:</strong> {{ $client->created_at->diffForHumans() }}
                </div>
                <div class="mb-3">
                    <strong>Total Savings:</strong> ${{ number_format($stats['total_savings'], 2) }}
                </div>
                <div class="mb-3">
                    <strong>Outstanding Balance:</strong> ${{ number_format($stats['outstanding_balance'], 2) }}
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
