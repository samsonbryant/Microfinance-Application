@extends('layouts.app')

@section('title', 'Accounting Dashboard - Microbook-G5')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-chart-line me-2"></i>Accounting Dashboard - Microbook-G5</h4>
                <div class="btn-group">
                    <a href="{{ route('accounting.financial-reports') }}" class="btn btn-primary">
                        <i class="fas fa-file-alt me-2"></i>Financial Reports
                    </a>
                    <a href="{{ route('accounting.chart-of-accounts') }}" class="btn btn-secondary">
                        <i class="fas fa-list me-2"></i>Chart of Accounts
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $dashboardData['revenue']['formatted_amount'] ?? '$0.00' }}</h4>
                            <p class="mb-0">Revenue (This Month)</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-up fa-2x"></i>
                        </div>
                    </div>
                    @if(isset($dashboardData['revenue']['change_percentage']))
                        <small>
                            {{ $dashboardData['revenue']['change_percentage'] >= 0 ? '+' : '' }}{{ number_format($dashboardData['revenue']['change_percentage'], 1) }}% 
                            from last month
                        </small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $dashboardData['expenses']['formatted_amount'] ?? '$0.00' }}</h4>
                            <p class="mb-0">Expenses (This Month)</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-down fa-2x"></i>
                        </div>
                    </div>
                    @if(isset($dashboardData['expenses']['change_percentage']))
                        <small>
                            {{ $dashboardData['expenses']['change_percentage'] >= 0 ? '+' : '' }}{{ number_format($dashboardData['expenses']['change_percentage'], 1) }}% 
                            from last month
                        </small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-{{ ($dashboardData['net_income']['current'] ?? 0) >= 0 ? 'success' : 'warning' }} text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $dashboardData['net_income']['formatted_amount'] ?? '$0.00' }}</h4>
                            <p class="mb-0">Net Income (This Month)</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                    @if(isset($dashboardData['net_income']['change_percentage']))
                        <small>
                            {{ $dashboardData['net_income']['change_percentage'] >= 0 ? '+' : '' }}{{ number_format($dashboardData['net_income']['change_percentage'], 1) }}% 
                            from last month
                        </small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $pendingApprovals->count() }}</h4>
                            <p class="mb-0">Pending Approvals</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <small>Requires your attention</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Transactions -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-exchange-alt me-2"></i>Recent Transactions
                    </h6>
                </div>
                <div class="card-body">
                    @if($recentTransactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Account</th>
                                        <th>Description</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTransactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->transaction_date->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $transaction->account->getTypeBadgeClass() }}">
                                                    {{ $transaction->account->code }} - {{ $transaction->account->name }}
                                                </span>
                                            </td>
                                            <td>{{ Str::limit($transaction->description, 50) }}</td>
                                            <td class="text-end">
                                                @if($transaction->debit > 0)
                                                    <span class="text-danger">${{ number_format($transaction->debit, 2) }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($transaction->credit > 0)
                                                    <span class="text-success">${{ number_format($transaction->credit, 2) }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $transaction->user->name ?? 'System' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            <a href="{{ route('accounting.general-ledger') }}" class="btn btn-sm btn-outline-primary">
                                View All Transactions
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No recent transactions found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-clock me-2"></i>Pending Approvals
                    </h6>
                </div>
                <div class="card-body">
                    @if($pendingApprovals->count() > 0)
                        @foreach($pendingApprovals as $approval)
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            @if($approval instanceof \App\Models\JournalEntry)
                                                Journal Entry #{{ $approval->journal_number }}
                                            @elseif($approval instanceof \App\Models\ExpenseEntry)
                                                Expense Entry #{{ $approval->expense_number }}
                                            @endif
                                        </h6>
                                        <p class="mb-1 text-muted small">{{ Str::limit($approval->description, 60) }}</p>
                                        <small class="text-muted">
                                            By: {{ $approval->user->name }} | {{ $approval->created_at->format('M d, Y H:i') }}
                                        </small>
                                    </div>
                                    <div class="btn-group-vertical btn-group-sm">
                                        @if($approval instanceof \App\Models\JournalEntry && Auth::user()->can('approve_journal_entries'))
                                            <form action="{{ route('accounting.journal-entries.approve', $approval) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm mb-1">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @elseif($approval instanceof \App\Models\ExpenseEntry && Auth::user()->can('approve_expenses'))
                                            <form action="{{ route('accounting.expense-entries.approve', $approval) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm mb-1">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">No pending approvals.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @can('manage_journal_entries')
                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="{{ route('accounting.journal-entries.create') }}" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-plus me-2"></i>Create Journal Entry
                                </a>
                            </div>
                        @endcan
                        
                        @can('manage_expenses')
                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="{{ route('accounting.expense-entries.create') }}" class="btn btn-outline-danger w-100">
                                    <i class="fas fa-receipt me-2"></i>Record Expense
                                </a>
                            </div>
                        @endcan
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('accounting.general-ledger') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-book me-2"></i>General Ledger
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('accounting.financial-reports') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-chart-bar me-2"></i>Financial Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
