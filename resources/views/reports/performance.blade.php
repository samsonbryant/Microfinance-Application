@extends('layouts.app')

@section('title', 'Performance Report')

@section('content')
<div class="page-header">
    <h1 class="page-title">Performance Report</h1>
    <p class="page-subtitle">Staff and branch performance analysis</p>
</div>

<!-- Loan Officer Performance -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-tie me-2"></i>Loan Officer Performance
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Branch</th>
                                <th>Loans Managed</th>
                                <th>Clients Managed</th>
                                <th>Total Disbursed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($performance['loan_officers'] as $officer)
                                <tr>
                                    <td>{{ $officer['name'] }}</td>
                                    <td>{{ $officer['branch'] }}</td>
                                    <td>{{ $officer['loans_count'] }}</td>
                                    <td>{{ $officer['clients_count'] }}</td>
                                    <td>${{ number_format($officer['total_disbursed'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No loan officers found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Branch Performance -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-building me-2"></i>Branch Performance
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Branch Name</th>
                                <th>Total Loans</th>
                                <th>Total Clients</th>
                                <th>Portfolio Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branchPerformance as $branch)
                                <tr>
                                    <td>{{ $branch['name'] }}</td>
                                    <td>{{ $branch['loans_count'] }}</td>
                                    <td>{{ $branch['clients_count'] }}</td>
                                    <td>${{ number_format($branch['total_portfolio'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No branches found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Targets -->
<div class="row">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bullseye me-2"></i>Monthly Targets
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Loans Disbursed</span>
                        <strong>{{ $performance['monthly_targets']['loans_disbursed'] ?? 0 }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Collections Target</span>
                        <strong>${{ number_format($performance['monthly_targets']['collections'] ?? 0, 0) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>New Clients</span>
                        <strong>{{ $performance['monthly_targets']['new_clients'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-line me-2"></i>Achievement Rates
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Loans Disbursed</span>
                        <span class="badge bg-success">{{ $performance['achievement_rates']['loans_disbursed'] ?? 0 }}%</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Collections</span>
                        <span class="badge bg-success">{{ $performance['achievement_rates']['collections'] ?? 0 }}%</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>New Clients</span>
                        <span class="badge bg-info">{{ $performance['achievement_rates']['new_clients'] ?? 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
