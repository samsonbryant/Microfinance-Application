<div class="accounting-dashboard" wire:poll.10s>
    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">From Date</label>
                            <input type="date" wire:model.live="fromDate" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">To Date</label>
                            <input type="date" wire:model.live="toDate" class="form-control">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button wire:click="loadMetrics" class="btn btn-primary">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Revenue Card -->
        <div class="col-md-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75">Total Revenue</p>
                            <h3 class="mb-0 fw-bold">${{ number_format($profitLoss['total_revenue'] ?? 0, 2) }}</h3>
                        </div>
                        <div class="icon-box" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-arrow-up fa-2x"></i>
                        </div>
                    </div>
                    <small class="opacity-75">
                        <i class="fas fa-calendar"></i> {{ date('M d', strtotime($fromDate)) }} - {{ date('M d, Y', strtotime($toDate)) }}
                    </small>
                </div>
            </div>
        </div>

        <!-- Total Expenses Card -->
        <div class="col-md-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75">Total Expenses</p>
                            <h3 class="mb-0 fw-bold">${{ number_format($profitLoss['total_expenses'] ?? 0, 2) }}</h3>
                        </div>
                        <div class="icon-box" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-arrow-down fa-2x"></i>
                        </div>
                    </div>
                    <small class="opacity-75">
                        <i class="fas fa-calendar"></i> {{ date('M d', strtotime($fromDate)) }} - {{ date('M d, Y', strtotime($toDate)) }}
                    </small>
                </div>
            </div>
        </div>

        <!-- Net Income Card -->
        <div class="col-md-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #14B8A6 0%, #0D9488 100%); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75">Net Income</p>
                            <h3 class="mb-0 fw-bold">${{ number_format($profitLoss['net_income'] ?? 0, 2) }}</h3>
                        </div>
                        <div class="icon-box" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                    <small class="opacity-75">
                        Revenue - Expenses
                    </small>
                </div>
            </div>
        </div>

        <!-- Cash Position Card -->
        <div class="col-md-3">
            <div class="card text-white" style="background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75">Cash Position</p>
                            <h3 class="mb-0 fw-bold">${{ number_format($cashPosition['total_cash'] ?? 0, 2) }}</h3>
                        </div>
                        <div class="icon-box" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-wallet fa-2x"></i>
                        </div>
                    </div>
                    <small class="opacity-75">
                        All cash accounts
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Breakdown -->
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-chart-pie text-primary"></i> Revenue Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($revenueBreakdown as $type => $amount)
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                                <strong class="text-success">${{ number_format($amount, 2) }}</strong>
                            </div>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ $profitLoss['total_revenue'] > 0 ? ($amount / $profitLoss['total_revenue'] * 100) : 0 }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="col-md-4">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-clock text-warning"></i> Pending Approvals
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Expenses
                            <span class="badge bg-warning rounded-pill">{{ $pendingApprovals['expenses'] ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Revenues
                            <span class="badge bg-success rounded-pill">{{ $pendingApprovals['revenues'] ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Transfers
                            <span class="badge bg-info rounded-pill">{{ $pendingApprovals['transfers'] ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Journal Entries
                            <span class="badge bg-primary rounded-pill">{{ $pendingApprovals['journals'] ?? 0 }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-body">
                    <h6 class="mb-3 fw-semibold">Quick Actions</h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('accounting.expenses.create') }}" class="btn btn-outline-danger">
                            <i class="fas fa-plus"></i> New Expense
                        </a>
                        <a href="{{ route('accounting.revenues.create') }}" class="btn btn-outline-success">
                            <i class="fas fa-plus"></i> New Revenue
                        </a>
                        <a href="{{ route('accounting.transfers.create') }}" class="btn btn-outline-info">
                            <i class="fas fa-exchange-alt"></i> New Transfer
                        </a>
                        <a href="{{ route('accounting.reports.profit-loss') }}" class="btn btn-outline-primary">
                            <i class="fas fa-file-alt"></i> View P&L
                        </a>
                        <a href="{{ route('accounting.reports.balance-sheet') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-balance-scale"></i> Balance Sheet
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .accounting-dashboard {
        font-family: 'Inter', sans-serif;
    }
    
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 12px -2px rgba(0,0,0,0.15) !important;
    }
    
    .btn {
        border-radius: 8px;
        font-weight: 500;
    }
    
    .progress {
        border-radius: 4px;
        background-color: #E5E7EB;
    }
    
    .progress-bar {
        border-radius: 4px;
    }
</style>

