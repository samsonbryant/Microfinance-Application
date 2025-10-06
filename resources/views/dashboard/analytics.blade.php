@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Analytics Dashboard</h1>
    <p class="page-subtitle">Comprehensive analytics and insights for your microfinance operations.</p>
</div>

<!-- Analytics Overview Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-value">{{ $analytics['loan_performance']['approval_rate'] ?? 85.5 }}%</div>
            <div class="stat-label">Loan Approval Rate</div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="opacity-75">+5% from last month</small>
                <i class="fas fa-arrow-up"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-value">{{ $analytics['loan_performance']['default_rate'] ?? 2.3 }}%</div>
            <div class="stat-label">Default Rate</div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="opacity-75">-0.2% from last month</small>
                <i class="fas fa-arrow-down"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-value">${{ number_format(($analytics['loan_performance']['average_loan_size'] ?? 15000) / 1000, 1) }}K</div>
            <div class="stat-label">Avg Loan Size</div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="opacity-75">+8% from last month</small>
                <i class="fas fa-arrow-up"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-value">{{ $analytics['loan_performance']['average_repayment_period'] ?? 18 }}m</div>
            <div class="stat-label">Avg Repayment Period</div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="opacity-75">+2 months from last year</small>
                <i class="fas fa-arrow-up"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row mb-4">
    <!-- Loan Performance Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Loan Performance Trends</h6>
            </div>
            <div class="card-body">
                <canvas id="loanPerformanceChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Portfolio Distribution -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Portfolio Distribution</h6>
            </div>
            <div class="card-body">
                <canvas id="portfolioChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Risk Analysis -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Risk Analysis</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center">
                            <h4 class="text-warning">{{ $analytics['risk_analysis']['high_risk_loans'] ?? 12 }}</h4>
                            <p class="mb-0">High Risk Loans</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h4 class="text-info">{{ $analytics['risk_analysis']['medium_risk_loans'] ?? 28 }}</h4>
                            <p class="mb-0">Medium Risk Loans</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h4 class="text-success">{{ $analytics['risk_analysis']['low_risk_loans'] ?? 156 }}</h4>
                            <p class="mb-0">Low Risk Loans</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Profitability Analysis -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Profitability Analysis</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-primary">${{ number_format(($analytics['profitability']['total_revenue'] ?? 125000) / 1000, 1) }}K</h4>
                            <p class="mb-0">Total Revenue</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-success">${{ number_format(($analytics['profitability']['net_profit'] ?? 45000) / 1000, 1) }}K</h4>
                            <p class="mb-0">Net Profit</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-info">{{ $analytics['profitability']['profit_margin'] ?? 36 }}%</h4>
                            <p class="mb-0">Profit Margin</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-warning">${{ number_format(($analytics['profitability']['roi'] ?? 18.5) * 100, 1) }}%</h4>
                            <p class="mb-0">ROI</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Loan Performance Chart
    const loanCtx = document.getElementById('loanPerformanceChart').getContext('2d');
    new Chart(loanCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Loans Disbursed',
                data: [12, 19, 15, 25, 22, 30],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4
            }, {
                label: 'Loans Repaid',
                data: [8, 15, 12, 20, 18, 25],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Portfolio Distribution Chart
    const portfolioCtx = document.getElementById('portfolioChart').getContext('2d');
    new Chart(portfolioCtx, {
        type: 'doughnut',
        data: {
            labels: ['Agriculture', 'Small Business', 'Education', 'Housing', 'Other'],
            datasets: [{
                data: [35, 25, 20, 15, 5],
                backgroundColor: [
                    '#3b82f6',
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#8b5cf6'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
});
</script>
@endpush
@endsection
