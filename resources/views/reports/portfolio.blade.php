@extends('layouts.app')

@section('title', 'Portfolio Report')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-chart-pie me-2"></i>Portfolio Report</h4>
                <div class="btn-group">
                    <a href="{{ route('reports.export-excel', 'portfolio') }}" class="btn btn-outline-success">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </a>
                    <a href="{{ route('reports.export-pdf', 'portfolio') }}" class="btn btn-outline-danger">
                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter me-2"></i>Filters
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.portfolio') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="branch_id" class="form-label">Branch</label>
                                    <select class="form-select" id="branch_id" name="branch_id">
                                        <option value="">All Branches</option>
                                        @foreach(\App\Models\Branch::all() as $branch)
                                            <option value="{{ $branch->id }}" {{ $filters['branch_id'] == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">All Statuses</option>
                                        <option value="active" {{ $filters['status'] == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="overdue" {{ $filters['status'] == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                        <option value="completed" {{ $filters['status'] == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="defaulted" {{ $filters['status'] == 'defaulted' ? 'selected' : '' }}>Defaulted</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from" 
                                           value="{{ $filters['date_from'] ? $filters['date_from']->format('Y-m-d') : '' }}">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to" 
                                           value="{{ $filters['date_to'] ? $filters['date_to']->format('Y-m-d') : '' }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Apply Filters
                                </button>
                                <a href="{{ route('reports.portfolio') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Clear Filters
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Portfolio Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="stat-value">{{ number_format($portfolioStats['total_loans']) }}</div>
                    <div class="stat-label">Total Loans</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-value">${{ number_format($portfolioStats['total_amount'], 0) }}</div>
                    <div class="stat-label">Total Amount</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="stat-value">${{ number_format($portfolioStats['average_loan_size'], 0) }}</div>
                    <div class="stat-label">Average Loan Size</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value">{{ number_format($portfolioStats['overdue_loans']) }}</div>
                    <div class="stat-label">Overdue Loans</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Portfolio Distribution Chart -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>Loan Status Distribution
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i>Portfolio by Branch
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="branchChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Loans Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table me-2"></i>Loan Portfolio Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Loan ID</th>
                                    <th>Client</th>
                                    <th>Branch</th>
                                    <th>Amount</th>
                                    <th>Outstanding</th>
                                    <th>Interest Rate</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loans as $loan)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-alt text-primary me-2"></i>
                                                #{{ $loan->id }}
                                            </div>
                                        </td>
                                        <td>{{ $loan->client->full_name ?? 'N/A' }}</td>
                                        <td>{{ $loan->branch->name ?? 'N/A' }}</td>
                                        <td>${{ number_format($loan->principal_amount, 2) }}</td>
                                        <td>${{ number_format($loan->outstanding_balance, 2) }}</td>
                                        <td>{{ number_format($loan->interest_rate, 2) }}%</td>
                                        <td>
                                            <span class="badge bg-{{ $loan->status === 'active' ? 'success' : ($loan->status === 'overdue' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $loan->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No loans found matching the criteria</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($loans->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $loans->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Status Distribution Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Active', 'Overdue', 'Completed', 'Defaulted'],
        datasets: [{
            data: [
                {{ $portfolioStats['active_loans'] }},
                {{ $portfolioStats['overdue_loans'] }},
                {{ $portfolioStats['completed_loans'] }},
                0
            ],
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 159, 64, 0.8)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 2
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

// Branch Chart (simplified)
const branchCtx = document.getElementById('branchChart').getContext('2d');
const branchChart = new Chart(branchCtx, {
    type: 'bar',
    data: {
        labels: ['Branch 1', 'Branch 2', 'Branch 3'],
        datasets: [{
            label: 'Portfolio Value',
            data: [50000, 75000, 30000],
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
@endsection
