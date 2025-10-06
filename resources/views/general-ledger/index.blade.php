@extends('layouts.app')

@section('title', 'General Ledger')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-book me-2"></i>General Ledger</h4>
                <div class="btn-group">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newEntryModal">
                        <i class="fas fa-plus me-2"></i>New Entry
                    </button>
                    <a href="{{ route('general-ledger.trial-balance') }}" class="btn btn-outline-info">
                        <i class="fas fa-balance-scale me-2"></i>Trial Balance
                    </a>
                    <a href="{{ route('general-ledger.profit-loss') }}" class="btn btn-outline-success">
                        <i class="fas fa-chart-line me-2"></i>P&L Statement
                    </a>
                    <a href="{{ route('general-ledger.balance-sheet') }}" class="btn btn-outline-warning">
                        <i class="fas fa-file-invoice me-2"></i>Balance Sheet
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
                    <form method="GET" action="{{ route('general-ledger.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="branch_id" class="form-label">Branch</label>
                                    <select class="form-select" id="branch_id" name="branch_id">
                                        <option value="">All Branches</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}" {{ request('branch_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">All Status</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="reversed" {{ request('status') == 'reversed' ? 'selected' : '' }}>Reversed</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                                <a href="{{ route('general-ledger.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Entries -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>Ledger Entries
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Entry #</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Debit Account</th>
                                    <th>Credit Account</th>
                                    <th>Debit Amount</th>
                                    <th>Credit Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ledgerEntries as $entry)
                                    <tr>
                                        <td>{{ $entry->entry_number }}</td>
                                        <td>{{ $entry->entry_date->format('M d, Y') }}</td>
                                        <td>{{ $entry->description }}</td>
                                        <td>{{ $entry->debit_account_code }}</td>
                                        <td>{{ $entry->credit_account_code }}</td>
                                        <td class="text-end">${{ number_format($entry->debit_amount, 2) }}</td>
                                        <td class="text-end">${{ number_format($entry->credit_amount, 2) }}</td>
                                        <td>
                                            @switch($entry->status)
                                                @case('pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                    @break
                                                @case('approved')
                                                    <span class="badge bg-success">Approved</span>
                                                    @break
                                                @case('reversed')
                                                    <span class="badge bg-danger">Reversed</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-secondary">Cancelled</span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick="viewEntry({{ $entry->id }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if($entry->status === 'pending')
                                                    <button class="btn btn-outline-success" onclick="approveEntry({{ $entry->id }})">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No ledger entries found</p>
                                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newEntryModal">
                                                Create First Entry
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($ledgerEntries->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $ledgerEntries->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Entry Modal -->
<div class="modal fade" id="newEntryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Ledger Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="entry_date" class="form-label">Entry Date</label>
                                <input type="date" class="form-control" id="entry_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reference_type" class="form-label">Reference Type</label>
                                <select class="form-select" id="reference_type" required>
                                    <option value="">Select Type</option>
                                    <option value="loan">Loan</option>
                                    <option value="savings">Savings</option>
                                    <option value="transaction">Transaction</option>
                                    <option value="adjustment">Adjustment</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="debit_account" class="form-label">Debit Account</label>
                                <select class="form-select" id="debit_account" required>
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->code }}">{{ $account->code }} - {{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="credit_account" class="form-label">Credit Account</label>
                                <select class="form-select" id="credit_account" required>
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->code }}">{{ $account->code }} - {{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="debit_amount" class="form-label">Debit Amount</label>
                                <input type="number" class="form-control" id="debit_amount" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="credit_amount" class="form-label">Credit Amount</label>
                                <input type="number" class="form-control" id="credit_amount" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Create Entry</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function viewEntry(entryId) {
    // Implement view entry functionality
    alert('View entry: ' + entryId);
}

function approveEntry(entryId) {
    if (confirm('Are you sure you want to approve this entry?')) {
        // Implement approve entry functionality
        alert('Entry approved: ' + entryId);
    }
}
</script>
@endsection
