@extends('layouts.app')

@section('title', 'Reports & Analytics')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h4>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>Export as PDF</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Export as Excel</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Report Types -->
    <div class="row mb-4">
        @php
            $reportIcons = [
                'portfolio_at_risk' => ['icon' => 'fa-exclamation-triangle', 'color' => 'danger'],
                'loan_performance' => ['icon' => 'fa-chart-line', 'color' => 'success'],
                'client_demographics' => ['icon' => 'fa-users', 'color' => 'info'],
                'financial_summary' => ['icon' => 'fa-dollar-sign', 'color' => 'primary'],
                'branch_performance' => ['icon' => 'fa-building', 'color' => 'warning'],
                'collections_report' => ['icon' => 'fa-credit-card', 'color' => 'success'],
                'recovery_report' => ['icon' => 'fa-tools', 'color' => 'danger'],
                'audit_trail' => ['icon' => 'fa-history', 'color' => 'secondary'],
            ];
        @endphp

        @foreach($reportTypes as $key => $label)
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-{{ $reportIcons[$key]['color'] ?? 'primary' }} text-white h-100">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas {{ $reportIcons[$key]['icon'] ?? 'fa-chart-bar' }}"></i>
                    </div>
                    <div class="stat-label">{{ $label }}</div>
                    <div class="mt-3">
                        <a href="{{ route('accounting.reports.show', $key) }}" class="btn btn-light btn-sm">
                            <i class="fas fa-eye"></i> View Report
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @endforeach
    </div>

    <!-- Report Categories -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-pie fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Portfolio Reports</h5>
                    <p class="card-text">Comprehensive loan portfolio analysis and performance metrics.</p>
                    <a href="{{ route('reports.portfolio') }}" class="btn btn-primary">
                        <i class="fas fa-eye me-2"></i>View Portfolio Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-credit-card fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Collections Reports</h5>
                    <p class="card-text">Payment collection analysis and recovery performance tracking.</p>
                    <a href="{{ route('reports.collections') }}" class="btn btn-success">
                        <i class="fas fa-eye me-2"></i>View Collections Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Performance Reports</h5>
                    <p class="card-text">Staff and branch performance metrics and achievement tracking.</p>
                    <a href="{{ route('reports.performance') }}" class="btn btn-info">
                        <i class="fas fa-eye me-2"></i>View Performance Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Reports -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-file-invoice-dollar fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Financial Reports</h5>
                    <p class="card-text">Trial balance, profit & loss, and balance sheet reports.</p>
                    <a href="{{ route('reports.financial') }}" class="btn btn-warning">
                        <i class="fas fa-eye me-2"></i>View Financial Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-secondary mb-3"></i>
                    <h5 class="card-title">Client Reports</h5>
                    <p class="card-text">Client demographics, activity, and relationship analysis.</p>
                    <a href="{{ route('reports.clients') }}" class="btn btn-secondary">
                        <i class="fas fa-eye me-2"></i>View Client Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Recent Reports
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Report Name</th>
                                    <th>Type</th>
                                    <th>Generated</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentReports as $report)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-alt text-primary me-2"></i>
                                                {{ $report['name'] }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $report['type'] === 'portfolio' ? 'primary' : ($report['type'] === 'collections' ? 'success' : 'info') }}">
                                                {{ ucfirst($report['type']) }}
                                            </span>
                                        </td>
                                        <td>{{ $report['date']->format('M d, Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-success">Completed</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('reports.' . $report['type']) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="#" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No recent reports available</p>
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
</div>
@endsection