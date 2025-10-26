@extends('layouts.app')

@section('title', 'Dashboard - Microbook-G5')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-tachometer-alt me-2"></i>Dashboard - Microbook-G5</h4>
                <div class="btn-group">
                    <button type="button" class="btn btn-success" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                    <button type="button" class="btn btn-info" onclick="exportDashboard()">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Status Indicator -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-center" id="realtime-status">
                <div class="spinner-border spinner-border-sm me-2" role="status" id="loading-spinner">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span id="status-text">Connecting to real-time updates...</span>
                <div class="ms-auto">
                    <small class="text-muted" id="last-update">Last updated: Never</small>
                </div>
            </div>
        </div>
    </div>

    <!-- System Alerts -->
    <div class="row mb-4" id="system-alerts" style="display: none;">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>System Alerts</h6>
                <div id="alerts-content"></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>

    <!-- Financial Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="today-debits">$0.00</h4>
                            <p class="mb-0">Today's Debits</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-down fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="today-credits">$0.00</h4>
                            <p class="mb-0">Today's Credits</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-up fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="today-net">$0.00</h4>
                            <p class="mb-0">Today's Net</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-balance-scale fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="today-transactions">0</h4>
                            <p class="mb-0">Today's Transactions</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Balances -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="card-title text-success">
                        <i class="fas fa-money-bill-wave me-2"></i>Cash on Hand
                    </h6>
                    <h4 class="text-success" id="cash-balance">$0.00</h4>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="card-title text-primary">
                        <i class="fas fa-hand-holding-usd me-2"></i>Loan Portfolio
                    </h6>
                    <h4 class="text-primary" id="loan-balance">$0.00</h4>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card border-info">
                <div class="card-body">
                    <h6 class="card-title text-info">
                        <i class="fas fa-piggy-bank me-2"></i>Client Savings
                    </h6>
                    <h4 class="text-info" id="savings-balance">$0.00</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clock me-2"></i>Pending Approvals
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-warning rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <i class="fas fa-book text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0" id="pending-journals">0</h6>
                                    <small class="text-muted">Journal Entries</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-info rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <i class="fas fa-receipt text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0" id="pending-expenses">0</h6>
                                    <small class="text-muted">Expense Entries</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-success rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <i class="fas fa-balance-scale text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0" id="pending-reconciliations">0</h6>
                                    <small class="text-muted">Reconciliations</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Recent Activities
                    </h6>
                </div>
                <div class="card-body">
                    <div id="recent-activities">
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @can('view_financial_reports')
                            <a href="{{ route('accounting.dashboard') }}" class="btn btn-outline-primary">
                                <i class="fas fa-chart-bar me-2"></i>Accounting Dashboard
                            </a>
                        @endcan
                        @can('manage_reconciliations')
                            <a href="{{ route('accounting.reconciliations') }}" class="btn btn-outline-info">
                                <i class="fas fa-balance-scale me-2"></i>Reconciliations
                            </a>
                        @endcan
                        @can('view_financial_reports')
                            <a href="{{ route('accounting.reports') }}" class="btn btn-outline-success">
                                <i class="fas fa-file-alt me-2"></i>Financial Reports
                            </a>
                        @endcan
                        @can('view_audit_trail')
                            <a href="{{ route('accounting.audit-trail') }}" class="btn btn-outline-warning">
                                <i class="fas fa-history me-2"></i>Audit Trail
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-area me-2"></i>Daily Transactions (Last 30 Days)
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="dailyTransactionsChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>Portfolio Summary
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="portfolioChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let dashboardData = {};
let lastUpdate = null;
let realtimeInterval;

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    startRealtimeUpdates();
    initializeCharts();
});

// Load dashboard data
function loadDashboardData() {
    fetch('{{ route("dashboard.data") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                dashboardData = data.data;
                updateDashboard();
                lastUpdate = new Date();
                updateStatus('Connected', 'success');
            } else {
                updateStatus('Failed to load data', 'danger');
            }
        })
        .catch(error => {
            console.error('Error loading dashboard data:', error);
            updateStatus('Connection error', 'danger');
        });
}

// Start real-time updates
function startRealtimeUpdates() {
    realtimeInterval = setInterval(function() {
        if (lastUpdate) {
            fetch('{{ route("dashboard.realtime") }}?last_update=' + lastUpdate.toISOString())
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateRealtimeData(data.data);
                        lastUpdate = new Date(data.timestamp);
                        updateStatus('Connected', 'success');
                    }
                })
                .catch(error => {
                    console.error('Error getting real-time updates:', error);
                    updateStatus('Connection error', 'danger');
                });
        }
    }, 30000); // Update every 30 seconds
}

// Update dashboard with new data
function updateDashboard() {
    if (!dashboardData.financial_summary) return;

    const financial = dashboardData.financial_summary;
    
    // Update financial cards
    document.getElementById('today-debits').textContent = formatCurrency(financial.today.debits);
    document.getElementById('today-credits').textContent = formatCurrency(financial.today.credits);
    document.getElementById('today-net').textContent = formatCurrency(financial.today.net);
    document.getElementById('today-transactions').textContent = financial.today.transactions;

    // Update balances
    document.getElementById('cash-balance').textContent = formatCurrency(financial.current_balances.cash_on_hand);
    document.getElementById('loan-balance').textContent = formatCurrency(financial.current_balances.loan_portfolio);
    document.getElementById('savings-balance').textContent = formatCurrency(financial.current_balances.client_savings);

    // Update pending approvals
    if (dashboardData.pending_approvals) {
        document.getElementById('pending-journals').textContent = dashboardData.pending_approvals.journal_entries;
        document.getElementById('pending-expenses').textContent = dashboardData.pending_approvals.expense_entries;
        document.getElementById('pending-reconciliations').textContent = dashboardData.pending_approvals.reconciliations;
    }

    // Update recent activities
    updateRecentActivities();

    // Update system alerts
    updateSystemAlerts();
}

// Update real-time data
function updateRealtimeData(data) {
    if (data.activities) {
        // Add new activities to the list
        data.activities.forEach(activity => {
            addActivityToFeed(activity);
        });
    }

    if (data.pending_approvals) {
        // Update pending approvals
        document.getElementById('pending-journals').textContent = data.pending_approvals.journal_entries;
        document.getElementById('pending-expenses').textContent = data.pending_approvals.expense_entries;
        document.getElementById('pending-reconciliations').textContent = data.pending_approvals.reconciliations;
    }

    if (data.alerts) {
        // Update system alerts
        updateSystemAlerts(data.alerts);
    }
}

// Update recent activities
function updateRecentActivities() {
    const container = document.getElementById('recent-activities');
    if (!dashboardData.recent_activities || dashboardData.recent_activities.length === 0) {
        container.innerHTML = '<div class="text-center py-3 text-muted">No recent activities</div>';
        return;
    }

    const activitiesHtml = dashboardData.recent_activities.map(activity => `
        <div class="d-flex align-items-center mb-3">
            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                <i class="fas fa-user text-white"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-0">${activity.description}</h6>
                <small class="text-muted">${activity.user} • ${formatDateTime(activity.created_at)}</small>
            </div>
            <div>
                <span class="badge bg-${getActivityBadgeClass(activity.log_name)}">
                    ${activity.log_name.replace('_', ' ')}
                </span>
            </div>
        </div>
    `).join('');

    container.innerHTML = activitiesHtml;
}

// Add activity to feed
function addActivityToFeed(activity) {
    const container = document.getElementById('recent-activities');
    const activityHtml = `
        <div class="d-flex align-items-center mb-3 activity-item">
            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                <i class="fas fa-user text-white"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-0">${activity.description}</h6>
                <small class="text-muted">${activity.user} • ${formatDateTime(activity.created_at)}</small>
            </div>
            <div>
                <span class="badge bg-${getActivityBadgeClass(activity.log_name)}">
                    ${activity.log_name.replace('_', ' ')}
                </span>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('afterbegin', activityHtml);
    
    // Remove old activities if more than 10
    const activities = container.querySelectorAll('.activity-item');
    if (activities.length > 10) {
        activities[activities.length - 1].remove();
    }
}

// Update system alerts
function updateSystemAlerts(alerts = null) {
    const alertsData = alerts || dashboardData.system_alerts;
    const alertsContainer = document.getElementById('system-alerts');
    const alertsContent = document.getElementById('alerts-content');

    if (!alertsData || alertsData.length === 0) {
        alertsContainer.style.display = 'none';
        return;
    }

    const alertsHtml = alertsData.map(alert => `
        <div class="alert alert-${alert.type} mb-2">
            <strong>${alert.title}:</strong> ${alert.message}
        </div>
    `).join('');

    alertsContent.innerHTML = alertsHtml;
    alertsContainer.style.display = 'block';
}

// Update status indicator
function updateStatus(message, type) {
    const statusText = document.getElementById('status-text');
    const statusContainer = document.getElementById('realtime-status');
    const lastUpdateElement = document.getElementById('last-update');
    const spinner = document.getElementById('loading-spinner');

    statusText.textContent = message;
    statusContainer.className = `alert alert-${type} d-flex align-items-center`;
    lastUpdateElement.textContent = `Last updated: ${new Date().toLocaleTimeString()}`;
    
    if (type === 'success') {
        spinner.style.display = 'none';
    } else {
        spinner.style.display = 'block';
    }
}

// Initialize charts
function initializeCharts() {
    // Daily Transactions Chart
    const dailyCtx = document.getElementById('dailyTransactionsChart').getContext('2d');
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Debits',
                data: [],
                borderColor: 'rgb(220, 53, 69)',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.1
            }, {
                label: 'Credits',
                data: [],
                borderColor: 'rgb(40, 167, 69)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Portfolio Chart
    const portfolioCtx = document.getElementById('portfolioChart').getContext('2d');
    new Chart(portfolioCtx, {
        type: 'doughnut',
        data: {
            labels: ['Cash on Hand', 'Loan Portfolio', 'Client Savings'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: [
                    'rgb(40, 167, 69)',
                    'rgb(0, 123, 255)',
                    'rgb(23, 162, 184)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatDateTime(dateString) {
    return new Date(dateString).toLocaleString();
}

function getActivityBadgeClass(logName) {
    const classes = {
        'general_ledger_entry': 'primary',
        'journal_entry': 'info',
        'expense_entry': 'warning',
        'reconciliation': 'success',
        'chart_of_account': 'secondary'
    };
    return classes[logName] || 'secondary';
}

// Action functions
function refreshDashboard() {
    updateStatus('Refreshing...', 'info');
    loadDashboardData();
}

function exportDashboard() {
    window.open('{{ route("dashboard.export") }}?format=csv', '_blank');
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (realtimeInterval) {
        clearInterval(realtimeInterval);
    }
});
</script>
@endsection
