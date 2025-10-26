@extends('layouts.app')

@section('title', 'Pending Risk Assessments')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-clipboard-list text-warning me-2"></i>Pending Risk Assessments
            </h1>
            <p class="text-muted mb-0">Clients awaiting risk assessment</p>
        </div>
        <a href="{{ route('risk-assessment.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Assessments
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-header py-3 bg-warning">
            <h6 class="m-0 font-weight-bold">Clients Pending Assessment ({{ $clients->total() }})</h6>
        </div>
        <div class="card-body">
            @if($clients->count() > 0)
                <form action="{{ route('risk-assessment.batch-assess') }}" method="POST" id="batchAssessForm">
                    @csrf
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-double me-1"></i>Assess Selected Clients
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" onclick="toggleAll(this)">
                                    </th>
                                    <th>Client</th>
                                    <th>Contact</th>
                                    <th>Total Loans</th>
                                    <th>Active Loans</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clients as $client)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="client_ids[]" value="{{ $client->id }}" class="client-checkbox">
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $client->full_name }}</strong><br>
                                            <small class="text-muted">{{ $client->client_number }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $client->phone }}<br>
                                        <small class="text-muted">{{ $client->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $client->loans->count() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $client->loans->whereIn('status', ['active', 'disbursed'])->count() }}</span>
                                    </td>
                                    <td>
                                        <small>{{ $client->created_at->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="assessSingleClient({{ $client->id }})" title="Assess Now">
                                                <i class="fas fa-clipboard-check"></i> Assess
                                            </button>
                                            <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-info" title="View Client">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>

                <div class="mt-3">
                    {{ $clients->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h5 class="text-success">All Clients Assessed</h5>
                    <p class="text-muted">Great! All active clients have been risk assessed.</p>
                    <a href="{{ route('risk-assessment.index') }}" class="btn btn-primary">
                        <i class="fas fa-chart-bar me-1"></i>View All Assessments
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleAll(checkbox) {
    const checkboxes = document.querySelectorAll('.client-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

function assessSingleClient(clientId) {
    if (confirm('Are you sure you want to assess this client?')) {
        fetch(`/risk-assessment/clients/${clientId}/assess`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Assessment completed! Risk Score: ' + data.data.risk_score.toFixed(2) + '% - Level: ' + data.data.risk_level);
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

function reassessClient(clientId) {
    if (confirm('Are you sure you want to reassess this client?')) {
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

