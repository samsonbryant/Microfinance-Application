@extends('layouts.app')

@section('title', 'Risk Assessment Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-shield-alt text-danger me-2"></i>Risk Assessment Management
            </h1>
            <p class="text-muted mb-0">Manage client risk profiles and credit scoring</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('risk-assessment.pending') }}" class="btn btn-warning">
                <i class="fas fa-exclamation-triangle me-1"></i>Pending Assessments
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
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Assessed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_assessed'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Low Risk</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['low_risk'] }}</div>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Medium Risk</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['medium_risk'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">High Risk</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['high_risk'] + $stats['very_high_risk'] }}</div>
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
            <form method="GET" action="{{ route('risk-assessment.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search Client</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Client name or number...">
                    </div>
                    <div class="col-md-3">
                        <label for="risk_level" class="form-label">Risk Level</label>
                        <select class="form-select" id="risk_level" name="risk_level">
                            <option value="">All Levels</option>
                            <option value="low" {{ request('risk_level') === 'low' ? 'selected' : '' }}>Low Risk</option>
                            <option value="medium" {{ request('risk_level') === 'medium' ? 'selected' : '' }}>Medium Risk</option>
                            <option value="high" {{ request('risk_level') === 'high' ? 'selected' : '' }}>High Risk</option>
                            <option value="very_high" {{ request('risk_level') === 'very_high' ? 'selected' : '' }}>Very High Risk</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                            <a href="{{ route('risk-assessment.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Risk Profiles Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list me-2"></i>Client Risk Profiles</h6>
        </div>
        <div class="card-body">
            @if($profiles->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Risk Score</th>
                                <th>Risk Level</th>
                                <th>Last Assessed</th>
                                <th>Assessed By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($profiles as $profile)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $profile->client->full_name }}</strong><br>
                                        <small class="text-muted">{{ $profile->client->client_number }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-{{ $profile->getRiskLevelBadgeClass() }}" 
                                             role="progressbar" 
                                             style="width: {{ $profile->risk_score }}%"
                                             aria-valuenow="{{ $profile->risk_score }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            {{ number_format($profile->risk_score, 1) }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $profile->getRiskLevelBadgeClass() }}">
                                        {{ $profile->getRiskLevelText() }}
                                    </span>
                                </td>
                                <td>
                                    @if($profile->last_assessed)
                                        <small>{{ $profile->last_assessed->format('M d, Y') }}<br>
                                        <span class="text-muted">{{ $profile->last_assessed->diffForHumans() }}</span></small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $profile->assessedBy->name ?? 'System' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('risk-assessment.show', $profile) }}" class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-warning" 
                                                onclick="reassessClient({{ $profile->client_id }})" title="Reassess">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <a href="{{ route('clients.show', $profile->client_id) }}" class="btn btn-sm btn-success" title="View Client">
                                            <i class="fas fa-user"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $profiles->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-shield-alt fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Risk Assessments Found</h5>
                    <p class="text-muted">Start by assessing client risk profiles.</p>
                    <a href="{{ route('risk-assessment.pending') }}" class="btn btn-primary">
                        <i class="fas fa-clipboard-list me-1"></i>View Pending Assessments
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function reassessClient(clientId) {
    if (confirm('Are you sure you want to reassess this client\'s risk profile?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/risk-assessment/clients/${clientId}/reassess`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection

