<div>
    <div class="row mb-4">
        <!-- Total Clients -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value">{{ number_format($metrics['total_clients'] ?? 0) }}</div>
                <div class="stat-label">Total Clients</div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="opacity-75">+12% from last month</small>
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
        </div>

        <!-- Active Loans -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="stat-icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="stat-value">{{ number_format($metrics['active_loans'] ?? 0) }}</div>
                <div class="stat-label">Active Loans</div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="opacity-75">+8% from last month</small>
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
        </div>

        <!-- Portfolio Value -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">${{ number_format(($metrics['total_outstanding'] ?? 0) / 1000, 1) }}K</div>
                <div class="stat-label">Portfolio Value</div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="opacity-75">+15% from last month</small>
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
        </div>

        <!-- Overdue Loans -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-value">{{ number_format($metrics['overdue_loans'] ?? 0) }}</div>
                <div class="stat-label">Overdue Loans</div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="opacity-75">{{ $metrics['overdue_loans'] > 0 ? 'Needs attention' : 'All good' }}</small>
                    <i class="fas fa-{{ $metrics['overdue_loans'] > 0 ? 'exclamation-triangle' : 'check-circle' }}"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Portfolio at Risk Alert -->
    @if(isset($metrics['portfolio_at_risk']) && $metrics['portfolio_at_risk']['percentage'] > 5)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Portfolio at Risk Alert!</strong> 
                Your PAR (Portfolio at Risk) is {{ $metrics['portfolio_at_risk']['percentage'] }}% 
                ({{ $metrics['portfolio_at_risk']['overdue_count'] }} overdue loans worth ${{ number_format($metrics['portfolio_at_risk']['amount'], 2) }}).
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif

    <!-- Auto-refresh indicator -->
    <div class="text-center mb-3">
        <small class="text-muted">
            <i class="fas fa-sync-alt fa-spin"></i> Auto-refreshing every {{ $refreshInterval }} seconds
        </small>
    </div>
</div>

<script>
document.addEventListener('livewire:init', () => {
    // Auto-refresh every 30 seconds
    setInterval(() => {
        Livewire.dispatch('refreshMetrics');
    }, {{ $refreshInterval * 1000 }});
});
</script>