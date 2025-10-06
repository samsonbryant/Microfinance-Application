@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Clients</h1>
    <p class="page-subtitle">Manage client information and KYC verification.</p>
</div>

<!-- Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2">
                <a href="{{ route('clients.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Client
                </a>
            </div>
            <div class="d-flex gap-2">
                <select class="form-select" style="width: 150px;" onchange="filterByStatus(this.value)">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
                <select class="form-select" style="width: 150px;" onchange="filterByKyc(this.value)">
                    <option value="">All KYC</option>
                    <option value="pending" {{ request('kyc_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="verified" {{ request('kyc_status') == 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="rejected" {{ request('kyc_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <input type="text" class="form-control" placeholder="Search clients..." style="width: 300px;">
                <button class="btn btn-outline-secondary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Clients Table -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Clients</h6>
    </div>
    <div class="card-body">
        @if($clients->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Client #</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Occupation</th>
                            <th>Income</th>
                            <th>Status</th>
                            <th>KYC Status</th>
                            <th>Branch</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                        <tr>
                            <td>
                                <strong>{{ $client->client_number }}</strong>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $client->full_name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ ucfirst($client->gender) }}, {{ $client->age }} years</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $client->email }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $client->phone }}</small>
                                </div>
                            </td>
                            <td>{{ $client->occupation }}</td>
                            <td>${{ number_format($client->monthly_income, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $client->status === 'active' ? 'success' : ($client->status === 'suspended' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($client->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $client->kyc_status === 'verified' ? 'success' : ($client->kyc_status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($client->kyc_status) }}
                                </span>
                            </td>
                            <td>{{ $client->branch->name ?? 'N/A' }}</td>
                            <td>{{ $client->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($client->kyc_status === 'pending')
                                        <form action="{{ route('clients.verify-kyc', $client) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Verify KYC">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($client->status === 'active')
                                        <form action="{{ route('clients.suspend', $client) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Suspend" onclick="return confirm('Are you sure you want to suspend this client?')">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                        </form>
                                    @elseif($client->status === 'suspended')
                                        <form action="{{ route('clients.activate', $client) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Activate">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('clients.destroy', $client) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this client?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $clients->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Clients Found</h5>
                <p class="text-muted">Start by adding your first client.</p>
                <a href="{{ route('clients.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add First Client
                </a>
            </div>
        @endif
    </div>
</div>

<script>
function filterByStatus(status) {
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    window.location = url;
}

function filterByKyc(kycStatus) {
    const url = new URL(window.location);
    if (kycStatus) {
        url.searchParams.set('kyc_status', kycStatus);
    } else {
        url.searchParams.delete('kyc_status');
    }
    window.location = url;
}
</script>
@endsection
