@extends('layouts.app')

@section('title', 'KYC Documents Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-id-card text-primary me-2"></i>KYC Documents Management
            </h1>
            <p class="text-muted mb-0">Manage client identity verification and compliance documents</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('kyc-documents.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Upload Document
            </a>
            <button type="button" class="btn btn-info" onclick="location.reload()">
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

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Documents</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $documents->total() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Verified</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $documents->where('verification_status', 'verified')->count() }}
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Review</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $documents->where('verification_status', 'pending')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Expiring Soon</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="expiring-soon-count">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header bg-light">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i>Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('kyc-documents.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="client" class="form-label">Client</label>
                        <select class="form-select" id="client" name="client_id">
                            <option value="">All Clients</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="document_type" class="form-label">Document Type</label>
                        <select class="form-select" id="document_type" name="document_type">
                            <option value="">All Types</option>
                            <option value="national_id" {{ request('document_type') === 'national_id' ? 'selected' : '' }}>National ID</option>
                            <option value="passport" {{ request('document_type') === 'passport' ? 'selected' : '' }}>Passport</option>
                            <option value="driving_license" {{ request('document_type') === 'driving_license' ? 'selected' : '' }}>Driving License</option>
                            <option value="utility_bill" {{ request('document_type') === 'utility_bill' ? 'selected' : '' }}>Utility Bill</option>
                            <option value="bank_statement" {{ request('document_type') === 'bank_statement' ? 'selected' : '' }}>Bank Statement</option>
                            <option value="other" {{ request('document_type') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="verification_status" class="form-label">Status</label>
                        <select class="form-select" id="verification_status" name="verification_status">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('verification_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="verified" {{ request('verification_status') === 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="rejected" {{ request('verification_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                            <a href="{{ route('kyc-documents.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list me-2"></i>KYC Documents</h6>
        </div>
        <div class="card-body">
            @if($documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Document Type</th>
                                <th>Document #</th>
                                <th>Status</th>
                                <th>Expiry Date</th>
                                <th>Uploaded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                            <tr>
                                <td><span class="badge bg-primary">#{{ $doc->id }}</span></td>
                                <td>
                                    <div>
                                        <strong>{{ $doc->client->full_name ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $doc->client->client_number ?? '' }}</small>
                                    </div>
                                </td>
                                <td><span class="badge bg-info">{{ $doc->document_type_name }}</span></td>
                                <td>{{ $doc->document_number ?? 'N/A' }}</td>
                                <td>{!! $doc->verification_status_badge !!}</td>
                                <td>
                                    @if($doc->expiry_date)
                                        <span class="{{ $doc->isExpired() ? 'text-danger' : ($doc->isExpiringSoon() ? 'text-warning' : '') }}">
                                            {{ $doc->expiry_date->format('M d, Y') }}
                                            @if($doc->isExpired())
                                                <i class="fas fa-exclamation-circle text-danger" title="Expired"></i>
                                            @elseif($doc->isExpiringSoon())
                                                <i class="fas fa-exclamation-triangle text-warning" title="Expiring Soon"></i>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $doc->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('kyc-documents.show', $doc) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('kyc-documents.download', $doc) }}" class="btn btn-sm btn-secondary" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @if($doc->verification_status === 'pending')
                                            <button type="button" class="btn btn-sm btn-success" onclick="verifyDocument({{ $doc->id }})" title="Verify">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('kyc-documents.edit', $doc) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteDocument({{ $doc->id }})" title="Delete">
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
                        Showing {{ $documents->firstItem() ?? 0 }} to {{ $documents->lastItem() ?? 0 }} of {{ $documents->total() }} results
                    </div>
                    <div>
                        {{ $documents->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-id-card fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No KYC Documents Found</h5>
                    <p class="text-muted">Start by uploading a client document.</p>
                    <a href="{{ route('kyc-documents.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Upload First Document
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function verifyDocument(docId) {
    if (confirm('Are you sure you want to verify this document?')) {
        fetch(`/kyc-documents/${docId}/verify`, {
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
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Network error occurred');
        });
    }
}

function deleteDocument(docId) {
    if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/kyc-documents/${docId}`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Count expiring documents
document.addEventListener('DOMContentLoaded', function() {
    const expiryDates = document.querySelectorAll('td span[class*="text-warning"]');
    document.getElementById('expiring-soon-count').textContent = expiryDates.length;
});
</script>
@endpush
@endsection
