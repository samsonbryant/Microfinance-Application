@extends('layouts.app')

@section('title', 'Expense Entries - Microbook-G5')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-receipt me-2"></i>Expense Entries - Microbook-G5</h4>
                <div class="btn-group">
                    @can('manage_expenses')
                        <a href="{{ route('accounting.expense-entries.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Record Expense
                        </a>
                    @endcan
                    <a href="{{ route('accounting.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $expenseEntries->where('status', 'pending')->count() }}</h4>
                            <p class="mb-0">Pending Approval</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $expenseEntries->where('status', 'approved')->count() }}</h4>
                            <p class="mb-0">Approved</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $expenseEntries->where('status', 'posted')->count() }}</h4>
                            <p class="mb-0">Posted</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-book fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">${{ number_format($expenseEntries->where('status', 'posted')->sum('amount'), 2) }}</h4>
                            <p class="mb-0">Total Expenses</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Entries Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>Expense Entries
                    </h6>
                </div>
                <div class="card-body">
                    @if($expenseEntries->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Expense #</th>
                                        <th>Date</th>
                                        <th>Account</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Reference</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenseEntries as $expense)
                                        <tr>
                                            <td>
                                                <code>{{ $expense->expense_number }}</code>
                                            </td>
                                            <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge bg-danger">
                                                    {{ $expense->account->code }} - {{ $expense->account->name }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;" title="{{ $expense->description }}">
                                                    {{ $expense->description }}
                                                </div>
                                                @if($expense->receipt_number)
                                                    <small class="text-muted">Receipt: {{ $expense->receipt_number }}</small>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-danger">${{ number_format($expense->amount, 2) }}</strong>
                                            </td>
                                            <td>
                                                @if($expense->reference_number)
                                                    <code>{{ $expense->reference_number }}</code>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $expense->getStatusBadgeClass() }}">
                                                    {{ ucfirst($expense->status) }}
                                                </span>
                                                @if($expense->approved_at)
                                                    <br><small class="text-muted">
                                                        Approved: {{ $expense->approved_at->format('M d, Y') }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $expense->user->name }}
                                                <br><small class="text-muted">{{ $expense->created_at->format('M d, Y') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-info" 
                                                            data-bs-toggle="modal" data-bs-target="#viewExpenseModal{{ $expense->id }}">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    @if($expense->canBeEdited() && Auth::id() == $expense->user_id)
                                                        <a href="{{ route('accounting.expense-entries.edit', $expense) }}" 
                                                           class="btn btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    
                                                    @if($expense->canBeApproved() && Auth::user()->can('approve_expenses'))
                                                        <form action="{{ route('accounting.expense-entries.approve', $expense) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-success" 
                                                                    onclick="return confirm('Are you sure you want to approve this expense entry?')">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    @if($expense->canBePosted() && Auth::user()->can('post_expenses'))
                                                        <form action="{{ route('accounting.expense-entries.post', $expense) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-primary" 
                                                                    onclick="return confirm('Are you sure you want to post this expense entry to the general ledger?')">
                                                                <i class="fas fa-book"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>

                                                <!-- View Expense Modal -->
                                                <div class="modal fade" id="viewExpenseModal{{ $expense->id }}" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Expense Entry Details</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <h6>Basic Information</h6>
                                                                        <table class="table table-sm">
                                                                            <tr>
                                                                                <td><strong>Expense Number:</strong></td>
                                                                                <td><code>{{ $expense->expense_number }}</code></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><strong>Date:</strong></td>
                                                                                <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><strong>Amount:</strong></td>
                                                                                <td><strong class="text-danger">${{ number_format($expense->amount, 2) }}</strong></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><strong>Status:</strong></td>
                                                                                <td>
                                                                                    <span class="badge bg-{{ $expense->getStatusBadgeClass() }}">
                                                                                        {{ ucfirst($expense->status) }}
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <h6>Account & References</h6>
                                                                        <table class="table table-sm">
                                                                            <tr>
                                                                                <td><strong>Account:</strong></td>
                                                                                <td>
                                                                                    <span class="badge bg-danger">
                                                                                        {{ $expense->account->code }} - {{ $expense->account->name }}
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><strong>Reference:</strong></td>
                                                                                <td>{{ $expense->reference_number ?: 'N/A' }}</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><strong>Receipt:</strong></td>
                                                                                <td>{{ $expense->receipt_number ?: 'N/A' }}</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><strong>Branch:</strong></td>
                                                                                <td>{{ $expense->branch->name }}</td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                                <div class="row mt-3">
                                                                    <div class="col-12">
                                                                        <h6>Description</h6>
                                                                        <p>{{ $expense->description }}</p>
                                                                    </div>
                                                                </div>
                                                                @if($expense->approved_by)
                                                                    <div class="row mt-3">
                                                                        <div class="col-12">
                                                                            <h6>Approval Information</h6>
                                                                            <table class="table table-sm">
                                                                                <tr>
                                                                                    <td><strong>Approved By:</strong></td>
                                                                                    <td>{{ $expense->approvedBy->name }}</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td><strong>Approved At:</strong></td>
                                                                                    <td>{{ $expense->approved_at->format('M d, Y H:i') }}</td>
                                                                                </tr>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $expenseEntries->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No expense entries found</h5>
                            <p class="text-muted">Start by recording your first expense entry.</p>
                            @can('manage_expenses')
                                <a href="{{ route('accounting.expense-entries.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Record First Expense
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
