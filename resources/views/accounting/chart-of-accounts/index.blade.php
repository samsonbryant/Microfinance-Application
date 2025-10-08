@extends('layouts.app')

@section('title', 'Chart of Accounts - Microbook-G5')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-list me-2"></i>Chart of Accounts - Microbook-G5</h4>
                <div class="btn-group">
                    @can('manage_chart_of_accounts')
                        <a href="{{ route('accounting.chart-of-accounts.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add Account
                        </a>
                    @endcan
                    <a href="{{ route('accounting.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Types -->
    @foreach(['asset', 'liability', 'equity', 'revenue', 'expense'] as $type)
        @if(isset($accounts[$type]) && $accounts[$type]->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-{{ $accounts[$type]->first()->getTypeBadgeClass() }} text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-{{ $type === 'asset' ? 'building' : ($type === 'liability' ? 'balance-scale' : ($type === 'equity' ? 'hand-holding-usd' : ($type === 'revenue' ? 'arrow-up' : 'arrow-down'))) }} me-2"></i>
                                {{ ucfirst($type) }}s
                                <span class="badge bg-light text-dark ms-2">{{ $accounts[$type]->count() }} accounts</span>
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Code</th>
                                            <th>Account Name</th>
                                            <th>Category</th>
                                            <th>Normal Balance</th>
                                            <th>Current Balance</th>
                                            <th>Status</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($accounts[$type] as $account)
                                            <tr>
                                                <td>
                                                    <code>{{ $account->code }}</code>
                                                </td>
                                                <td>
                                                    <strong>{{ $account->name }}</strong>
                                                    @if($account->description)
                                                        <br><small class="text-muted">{{ $account->description }}</small>
                                                    @endif
                                                    @if($account->is_system_account)
                                                        <br><span class="badge bg-warning text-dark">System Account</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $account->getCategoryBadgeClass() }}">
                                                        {{ ucwords(str_replace('_', ' ', $account->category)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $account->normal_balance === 'debit' ? 'danger' : 'success' }}">
                                                        {{ strtoupper($account->normal_balance) }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <strong class="{{ $account->getCurrentBalance() >= 0 ? 'text-success' : 'text-danger' }}">
                                                        ${{ number_format($account->getCurrentBalance(), 2) }}
                                                    </strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $account->is_active ? 'success' : 'secondary' }}">
                                                        {{ $account->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @can('manage_chart_of_accounts')
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('accounting.chart-of-accounts.edit', $account) }}" 
                                                               class="btn btn-outline-primary" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            @if($account->canBeDeleted())
                                                                <button type="button" class="btn btn-outline-danger" 
                                                                        onclick="deleteAccount({{ $account->id }})" title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <!-- Summary Card -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calculator me-2"></i>Account Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach(['asset', 'liability', 'equity', 'revenue', 'expense'] as $type)
                            @if(isset($accounts[$type]) && $accounts[$type]->count() > 0)
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <div class="text-center">
                                        <h5 class="mb-1">{{ ucfirst($type) }}s</h5>
                                        <h4 class="text-{{ $accounts[$type]->first()->getTypeBadgeClass() }}">
                                            {{ $accounts[$type]->count() }}
                                        </h4>
                                        <small class="text-muted">accounts</small>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this chart of account? This action cannot be undone.</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> Only accounts with no transactions can be deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteAccountForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Account</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function deleteAccount(accountId) {
    const form = document.getElementById('deleteAccountForm');
    form.action = `/accounting/chart-of-accounts/${accountId}`;
    const modal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
    modal.show();
}
</script>
@endsection
