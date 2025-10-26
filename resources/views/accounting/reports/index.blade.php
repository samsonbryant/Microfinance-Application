@extends('layouts.app')

@section('title', 'Financial Reports - Microbook-G5')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-chart-bar me-2"></i>Financial Reports - Microbook-G5</h4>
                <a href="{{ route('accounting.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Report Categories -->
    @foreach($reportTypes as $category => $reports)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-{{ $this->getCategoryColor($category) }} text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-{{ $this->getCategoryIcon($category) }} me-2"></i>
                            {{ ucwords(str_replace('_', ' ', $category)) }} Reports
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($reports as $reportKey => $reportName)
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="fas fa-{{ $this->getReportIcon($reportKey) }} fa-3x text-{{ $this->getCategoryColor($category) }}"></i>
                                            </div>
                                            <h6 class="card-title">{{ $reportName }}</h6>
                                            <p class="card-text text-muted small">
                                                {{ $this->getReportDescription($reportKey) }}
                                            </p>
                                            <div class="btn-group w-100">
                                                <a href="{{ route('accounting.reports.show', $reportKey) }}" 
                                                   class="btn btn-outline-{{ $this->getCategoryColor($category) }} btn-sm">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
                                                @can('export_financial_reports')
                                                    <div class="btn-group btn-sm" role="group">
                                                        <button type="button" class="btn btn-outline-{{ $this->getCategoryColor($category) }} dropdown-toggle" 
                                                                data-bs-toggle="dropdown">
                                                            <i class="fas fa-download me-1"></i>Export
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('accounting.reports.export', [$reportKey, 'pdf']) }}">
                                                                    <i class="fas fa-file-pdf me-2"></i>PDF
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('accounting.reports.export', [$reportKey, 'excel']) }}">
                                                                    <i class="fas fa-file-excel me-2"></i>Excel
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('accounting.financial-reports') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-chart-line me-2"></i>Financial Statements
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('accounting.reconciliations') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-balance-scale me-2"></i>Reconciliations
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('accounting.general-ledger') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-book me-2"></i>General Ledger
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('accounting.chart-of-accounts') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-list me-2"></i>Chart of Accounts
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Helper functions for report display
function getCategoryColor(category) {
    const colors = {
        'financial': 'primary',
        'operational': 'success',
        'reconciliation': 'info',
        'compliance': 'warning'
    };
    return colors[category] || 'secondary';
}

function getCategoryIcon(category) {
    const icons = {
        'financial': 'chart-line',
        'operational': 'cogs',
        'reconciliation': 'balance-scale',
        'compliance': 'shield-alt'
    };
    return icons[category] || 'file-alt';
}

function getReportIcon(reportKey) {
    const icons = {
        'profit_loss': 'chart-line',
        'balance_sheet': 'balance-scale',
        'trial_balance': 'calculator',
        'cash_flow': 'money-bill-wave',
        'loan_portfolio_aging': 'clock',
        'provisioning_report': 'exclamation-triangle',
        'delinquency_report': 'exclamation-circle',
        'collection_report': 'hand-holding-usd',
        'reconciliation_summary': 'list-check',
        'overdue_reconciliations': 'clock',
        'audit_trail': 'history',
        'user_activity': 'users',
        'transaction_log': 'list'
    };
    return icons[reportKey] || 'file-alt';
}

function getReportDescription(reportKey) {
    const descriptions = {
        'profit_loss': 'Income and expense statement',
        'balance_sheet': 'Assets, liabilities, and equity',
        'trial_balance': 'Account balances verification',
        'cash_flow': 'Cash inflows and outflows',
        'loan_portfolio_aging': 'Loan aging analysis',
        'provisioning_report': 'Loan loss provisions',
        'delinquency_report': 'Overdue loan analysis',
        'collection_report': 'Collection activities',
        'reconciliation_summary': 'Reconciliation status',
        'overdue_reconciliations': 'Pending reconciliations',
        'audit_trail': 'System activity log',
        'user_activity': 'User action tracking',
        'transaction_log': 'Transaction history'
    };
    return descriptions[reportKey] || 'Report description';
}
</script>
@endsection
