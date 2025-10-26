@extends('layouts.app')

@section('title', 'Loan Repayments')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-money-bill-wave text-success me-2"></i>Loan Repayments
            </h1>
            <p class="text-muted mb-0">Manage and track all loan repayments in real-time</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-info" onclick="refreshRepayments()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Due Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="due-today-count">
                                {{ $dueToday->count() }}
                            </div>
                            <small class="text-muted">${{ number_format($dueToday->sum('next_payment_amount'), 2) }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Overdue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="overdue-count">
                                {{ $overdue->count() }}
                            </div>
                            <small class="text-muted">${{ number_format($overdue->sum('outstanding_balance'), 2) }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Loans
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="active-count">
                                {{ $activeLoans->count() }}
                            </div>
                            <small class="text-muted">${{ number_format($activeLoans->sum('outstanding_balance'), 2) }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Expected
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($activeLoans->sum('next_payment_amount') ?? 0, 2) }}
                            </div>
                            <small class="text-muted">Next cycle</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs for different views -->
    <ul class="nav nav-tabs mb-3" id="repaymentTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="due-today-tab" data-bs-toggle="tab" data-bs-target="#due-today-pane" type="button">
                <i class="fas fa-clock me-1"></i>Due Today <span class="badge bg-warning">{{ $dueToday->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#overdue-pane" type="button">
                <i class="fas fa-exclamation-triangle me-1"></i>Overdue <span class="badge bg-danger">{{ $overdue->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming-pane" type="button">
                <i class="fas fa-calendar-alt me-1"></i>Upcoming <span class="badge bg-info">{{ $upcoming->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-pane" type="button">
                <i class="fas fa-list me-1"></i>All Active
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="repaymentTabsContent">
        <!-- Due Today -->
        <div class="tab-pane fade show active" id="due-today-pane" role="tabpanel">
            <div class="card shadow">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">Loans Due Today</h6>
                </div>
                <div class="card-body">
                    @include('loan-repayments.partials.loans-table', ['loans' => $dueToday, 'type' => 'due'])
                </div>
            </div>
        </div>

        <!-- Overdue -->
        <div class="tab-pane fade" id="overdue-pane" role="tabpanel">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">Overdue Loans</h6>
                </div>
                <div class="card-body">
                    @include('loan-repayments.partials.loans-table', ['loans' => $overdue, 'type' => 'overdue'])
                </div>
            </div>
        </div>

        <!-- Upcoming -->
        <div class="tab-pane fade" id="upcoming-pane" role="tabpanel">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Upcoming Payments (Next 30 Days)</h6>
                </div>
                <div class="card-body">
                    @include('loan-repayments.partials.loans-table', ['loans' => $upcoming, 'type' => 'upcoming'])
                </div>
            </div>
        </div>

        <!-- All Active -->
        <div class="tab-pane fade" id="all-pane" role="tabpanel">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">All Active Loans</h6>
                </div>
                <div class="card-body">
                    @if($activeLoans->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" id="allLoansTable">
                                <thead>
                                    <tr>
                                        <th>Loan #</th>
                                        <th>Client</th>
                                        <th>Outstanding</th>
                                        <th>Next Payment</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeLoans as $loan)
                                    <tr>
                                        <td><strong>{{ $loan->loan_number }}</strong></td>
                                        <td>
                                            {{ $loan->client->full_name ?? 'N/A' }}<br>
                                            <small class="text-muted">{{ $loan->client->phone }}</small>
                                        </td>
                                        <td><strong class="text-danger">${{ number_format($loan->outstanding_balance ?? 0, 2) }}</strong></td>
                                        <td>${{ number_format($loan->next_payment_amount ?? 0, 2) }}</td>
                                        <td>{{ $loan->next_due_date ? $loan->next_due_date->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            @if($loan->next_due_date && $loan->next_due_date < now())
                                                <span class="badge bg-danger">Overdue</span>
                                            @elseif($loan->next_due_date && $loan->next_due_date->isToday())
                                                <span class="badge bg-warning">Due Today</span>
                                            @else
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('loans.repayment', $loan) }}" class="btn btn-sm btn-success" title="Make Payment">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $activeLoans->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>No Active Loans</h5>
                            <p class="text-muted">All loans are either pending or completed.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Real-time refresh functionality
function refreshRepayments() {
    location.reload();
}

// Auto-refresh every 30 seconds
setInterval(function() {
    updateCounts();
}, 30000);

function updateCounts() {
    fetch('{{ route("loan-repayments.stats") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('due-today-count').textContent = data.due_today_count;
                document.getElementById('overdue-count').textContent = data.overdue_count;
                document.getElementById('active-count').textContent = data.active_count;
            }
        })
        .catch(error => console.error('Error updating counts:', error));
}

// Initialize DataTable for all loans
$(document).ready(function() {
    if (typeof $.fn.DataTable !== 'undefined' && $('#allLoansTable').length) {
        $('#allLoansTable').DataTable({
            "pageLength": 25,
            "order": [[4, "asc"]], // Order by due date
            "columnDefs": [
                { "orderable": false, "targets": [6] }
            ]
        });
    }
});
</script>
@endpush
@endsection
