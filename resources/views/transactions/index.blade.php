@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Transactions</h1>
    <p class="page-subtitle">Manage all financial transactions across the system.</p>
</div>

<!-- Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2">
                <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Transaction
                </a>
            </div>
            <div class="d-flex gap-2">
                <select class="form-select" style="width: 150px;">
                    <option value="">All Types</option>
                    <option value="deposit">Deposit</option>
                    <option value="withdrawal">Withdrawal</option>
                    <option value="loan_disbursement">Loan Disbursement</option>
                    <option value="loan_repayment">Loan Repayment</option>
                    <option value="transfer">Transfer</option>
                    <option value="fee">Fee</option>
                </select>
                <select class="form-select" style="width: 150px;">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="reversed">Reversed</option>
                </select>
                <input type="text" class="form-control" placeholder="Search transactions..." style="width: 300px;">
                <button class="btn btn-outline-secondary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Transactions Table -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Transactions</h6>
    </div>
    <div class="card-body">
        @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Transaction #</th>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Branch</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                        <tr>
                            <td>
                                <strong>{{ $transaction->transaction_number }}</strong>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $transaction->client->full_name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $transaction->client->client_number ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ 
                                    $transaction->type === 'deposit' ? 'success' : 
                                    ($transaction->type === 'withdrawal' ? 'warning' : 
                                    ($transaction->type === 'loan_disbursement' ? 'info' : 
                                    ($transaction->type === 'loan_repayment' ? 'primary' : 'secondary'))) 
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                </span>
                            </td>
                            <td>
                                <strong class="text-{{ $transaction->type === 'deposit' || $transaction->type === 'loan_repayment' ? 'success' : 'danger' }}">
                                    {{ $transaction->type === 'deposit' || $transaction->type === 'loan_repayment' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                                </strong>
                            </td>
                            <td>{{ Str::limit($transaction->description, 50) }}</td>
                            <td>
                                <span class="badge bg-{{ 
                                    $transaction->status === 'approved' ? 'success' : 
                                    ($transaction->status === 'pending' ? 'warning' : 
                                    ($transaction->status === 'rejected' ? 'danger' : 'secondary')) 
                                }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td>{{ $transaction->branch->name ?? 'N/A' }}</td>
                            <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($transaction->status === 'pending')
                                        <form action="{{ route('transactions.approve', $transaction) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($transaction->status === 'approved')
                                        <form action="{{ route('transactions.reverse', $transaction) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Reverse" onclick="return confirm('Are you sure you want to reverse this transaction?')">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $transactions->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Transactions Found</h5>
                <p class="text-muted">Start by creating a new transaction.</p>
                <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First Transaction
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
