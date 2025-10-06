@extends('layouts.app')

@section('title', 'Chart of Accounts')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-list-alt me-2"></i>Chart of Accounts</h4>
                <div class="btn-group">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAccountModal">
                        <i class="fas fa-plus me-2"></i>New Account
                    </button>
                    <button class="btn btn-outline-success" onclick="exportAccounts()">
                        <i class="fas fa-file-excel me-2"></i>Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Types -->
    @foreach($accounts as $type => $typeAccounts)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-folder me-2"></i>{{ ucfirst($type) }} Accounts
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Parent</th>
                                        <th>Normal Balance</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($typeAccounts as $account)
                                        <tr>
                                            <td>
                                                <strong>{{ $account->code }}</strong>
                                            </td>
                                            <td>{{ $account->name }}</td>
                                            <td>
                                                @if($account->parent)
                                                    {{ $account->parent->name }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $account->normal_balance === 'debit' ? 'bg-primary' : 'bg-success' }}">
                                                    {{ ucfirst($account->normal_balance) }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <strong>${{ number_format($account->getBalance(), 2) }}</strong>
                                            </td>
                                            <td>
                                                @if($account->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="viewAccount({{ $account->id }})">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning" onclick="editAccount({{ $account->id }})">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info" onclick="toggleStatus({{ $account->id }})">
                                                        <i class="fas fa-toggle-{{ $account->is_active ? 'on' : 'off' }}"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="deleteAccount({{ $account->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
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
    @endforeach
</div>

<!-- New Account Modal -->
<div class="modal fade" id="newAccountModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_code" class="form-label">Account Code</label>
                                <input type="text" class="form-control" id="account_code" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_type" class="form-label">Account Type</label>
                                <select class="form-select" id="account_type" required>
                                    <option value="">Select Type</option>
                                    <option value="asset">Asset</option>
                                    <option value="liability">Liability</option>
                                    <option value="equity">Equity</option>
                                    <option value="revenue">Revenue</option>
                                    <option value="expense">Expense</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="account_name" class="form-label">Account Name</label>
                        <input type="text" class="form-control" id="account_name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="parent_account" class="form-label">Parent Account</label>
                                <select class="form-select" id="parent_account">
                                    <option value="">No Parent</option>
                                    @foreach($accounts->flatten() as $account)
                                        <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="normal_balance" class="form-label">Normal Balance</label>
                                <select class="form-select" id="normal_balance" required>
                                    <option value="">Select Balance</option>
                                    <option value="debit">Debit</option>
                                    <option value="credit">Credit</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="account_description" class="form-label">Description</label>
                        <textarea class="form-control" id="account_description" rows="3"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" checked>
                        <label class="form-check-label" for="is_active">
                            Active
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Create Account</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function viewAccount(accountId) {
    // Implement view account functionality
    alert('View account: ' + accountId);
}

function editAccount(accountId) {
    // Implement edit account functionality
    alert('Edit account: ' + accountId);
}

function toggleStatus(accountId) {
    if (confirm('Are you sure you want to toggle the status of this account?')) {
        // Implement toggle status functionality
        alert('Account status toggled: ' + accountId);
    }
}

function deleteAccount(accountId) {
    if (confirm('Are you sure you want to delete this account? This action cannot be undone.')) {
        // Implement delete account functionality
        alert('Account deleted: ' + accountId);
    }
}

function exportAccounts() {
    // Implement export functionality
    alert('Exporting accounts...');
}
</script>
@endsection
