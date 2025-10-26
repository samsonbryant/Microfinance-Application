@extends('layouts.app')

@section('title', 'Risk Assessment Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-line text-danger me-2"></i>Risk Assessment Details
            </h1>
            <p class="text-muted mb-0">{{ $riskProfile->client->full_name }}</p>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-warning" onclick="reassessClient({{ $riskProfile->client_id }})">
                <i class="fas fa-sync-alt me-1"></i>Reassess
            </button>
            <a href="{{ route('risk-assessment.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <!-- Overall Risk Score -->
            <div class="card shadow mb-4">
                <div class="card-header bg-{{ $riskProfile->getRiskLevelBadgeClass() }} text-white">
                    <h6 class="m-0 font-weight-bold">Overall Risk Assessment</h6>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 mb-3">
                        <span class="badge bg-{{ $riskProfile->getRiskLevelBadgeClass() }}" style="font-size: 2rem;">
                            {{ number_format($riskProfile->risk_score, 1) }}%
                        </span>
                    </div>
                    <h5 class="text-{{ $riskProfile->getRiskLevelBadgeClass() }}">
                        {{ $riskProfile->getRiskLevelText() }}
                    </h5>
                    <hr>
                    <p class="text-muted mb-0">
                        <small>Last Assessed: {{ $riskProfile->last_assessed->format('M d, Y H:i') }}</small><br>
                        <small>By: {{ $riskProfile->assessedBy->name ?? 'System' }}</small>
                    </p>
                </div>
            </div>

            <!-- Client Summary -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Client Information</h6>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong><br>{{ $riskProfile->client->full_name }}</p>
                    <p><strong>Client #:</strong><br>{{ $riskProfile->client->client_number }}</p>
                    <p><strong>Phone:</strong><br>{{ $riskProfile->client->phone }}</p>
                    <p><strong>Total Loans:</strong><br>{{ $riskProfile->client->loans->count() }}</p>
                    <p><strong>Active Loans:</strong><br>{{ $riskProfile->client->loans->whereIn('status', ['active', 'disbursed'])->count() }}</p>
                    <a href="{{ route('clients.show', $riskProfile->client_id) }}" class="btn btn-sm btn-success">
                        <i class="fas fa-user me-1"></i>View Client
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Risk Factors Breakdown -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Risk Factors Breakdown</h6>
                </div>
                <div class="card-body">
                    @if($riskProfile->risk_factors && is_array($riskProfile->risk_factors))
                        @foreach($riskProfile->risk_factors as $factor => $score)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong>{{ ucfirst(str_replace('_', ' ', $factor)) }}</strong>
                                    <span class="badge bg-{{ $score >= 70 ? 'success' : ($score >= 40 ? 'warning' : 'danger') }}">
                                        {{ number_format($score, 1) }}/100
                                    </span>
                                </div>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $score >= 70 ? 'success' : ($score >= 40 ? 'warning' : 'danger') }}" 
                                         role="progressbar" 
                                         style="width: {{ $score }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No detailed risk factors available</p>
                    @endif
                </div>
            </div>

            <!-- Loan History -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Loan History</h6>
                </div>
                <div class="card-body">
                    @if($riskProfile->client->loans->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Loan #</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Outstanding</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($riskProfile->client->loans->take(10) as $loan)
                                    <tr>
                                        <td>{{ $loan->loan_number }}</td>
                                        <td>${{ number_format($loan->amount, 2) }}</td>
                                        <td><span class="badge bg-{{ $loan->status === 'completed' ? 'success' : ($loan->status === 'active' ? 'primary' : 'warning') }}">{{ ucfirst($loan->status) }}</span></td>
                                        <td>${{ number_format($loan->outstanding_balance ?? 0, 2) }}</td>
                                        <td><small>{{ $loan->created_at->format('M d, Y') }}</small></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No loan history available</p>
                    @endif
                </div>
            </div>

            <!-- Recommendations -->
            @if($riskProfile->recommendations)
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="m-0 font-weight-bold">Recommendations</h6>
                </div>
                <div class="card-body">
                    @if(is_array($riskProfile->recommendations))
                        <ul>
                            @foreach($riskProfile->recommendations as $recommendation)
                                <li>{{ $recommendation }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p>{{ $riskProfile->recommendations }}</p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function reassessClient(clientId) {
    if (confirm('Are you sure you want to reassess this client\'s risk profile? This will recalculate all risk factors.')) {
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

