@extends('layouts.app')

@section('title', 'Approval Center')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-check-double text-success me-2"></i>Unified Approval Center
            </h1>
            <p class="text-muted mb-0">Approve or reject all pending items in one place</p>
        </div>
        <button type="button" class="btn btn-info" onclick="refreshApprovals()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-pending">{{ $stats['total_pending'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clipboard-list fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Loans</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pending-loans">{{ $stats['pending_loans'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Savings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pending-savings">{{ $stats['pending_savings'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-piggy-bank fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">KYC Docs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pending-kyc">{{ $stats['pending_kyc'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-id-card fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Collateral</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pending-collateral">{{ $stats['pending_collateral'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-shield-alt fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Clients</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pending-clients">{{ $stats['pending_clients'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" id="approvalTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-pane" type="button">
                All <span class="badge bg-primary">{{ $stats['total_pending'] }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="loans-tab" data-bs-toggle="tab" data-bs-target="#loans-pane" type="button">
                Loans <span class="badge bg-info">{{ $stats['pending_loans'] }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="savings-tab" data-bs-toggle="tab" data-bs-target="#savings-pane" type="button">
                Savings <span class="badge bg-success">{{ $stats['pending_savings'] }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="kyc-tab" data-bs-toggle="tab" data-bs-target="#kyc-pane" type="button">
                KYC Documents <span class="badge bg-warning">{{ $stats['pending_kyc'] }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="collateral-tab" data-bs-toggle="tab" data-bs-target="#collateral-pane" type="button">
                Collateral <span class="badge bg-danger">{{ $stats['pending_collateral'] }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="clients-tab" data-bs-toggle="tab" data-bs-target="#clients-pane" type="button">
                Clients <span class="badge bg-secondary">{{ $stats['pending_clients'] }}</span>
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="approvalTabsContent">
        <!-- All Tab -->
        <div class="tab-pane fade show active" id="all-pane">
            @include('approvals.partials.all-pending', [
                'pendingLoans' => $pendingLoans,
                'pendingSavings' => $pendingSavings,
                'pendingKycDocs' => $pendingKycDocs,
                'pendingCollateral' => $pendingCollateral,
                'pendingClients' => $pendingClients
            ])
        </div>

        <!-- Loans Tab -->
        <div class="tab-pane fade" id="loans-pane">
            @include('approvals.partials.loans', ['items' => $pendingLoans])
        </div>

        <!-- Savings Tab -->
        <div class="tab-pane fade" id="savings-pane">
            @include('approvals.partials.savings', ['items' => $pendingSavings])
        </div>

        <!-- KYC Tab -->
        <div class="tab-pane fade" id="kyc-pane">
            @include('approvals.partials.kyc', ['items' => $pendingKycDocs])
        </div>

        <!-- Collateral Tab -->
        <div class="tab-pane fade" id="collateral-pane">
            @include('approvals.partials.collateral', ['items' => $pendingCollateral])
        </div>

        <!-- Clients Tab -->
        <div class="tab-pane fade" id="clients-pane">
            @include('approvals.partials.clients', ['items' => $pendingClients])
        </div>
    </div>
</div>

@push('scripts')
<script>
function refreshApprovals() {
    fetch('{{ route("approval-center.stats") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total-pending').textContent = 
                    data.stats.pending_loans + data.stats.pending_savings + data.stats.pending_kyc + 
                    data.stats.pending_collateral + data.stats.pending_clients;
                document.getElementById('pending-loans').textContent = data.stats.pending_loans;
                document.getElementById('pending-savings').textContent = data.stats.pending_savings;
                document.getElementById('pending-kyc').textContent = data.stats.pending_kyc;
                document.getElementById('pending-collateral').textContent = data.stats.pending_collateral;
                document.getElementById('pending-clients').textContent = data.stats.pending_clients;
            }
        });
}

function approveLoan(loanId) {
    if (confirm('Approve this loan?')) {
        fetch(`/approval-center/loans/${loanId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', data.message);
            }
        });
    }
}

function rejectLoan(loanId) {
    const reason = prompt('Reason for rejection:');
    if (reason) {
        fetch(`/approval-center/loans/${loanId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', data.message);
            }
        });
    }
}

function approveSavings(savingsId) {
    if (confirm('Approve this savings account?')) {
        fetch(`/approval-center/savings/${savingsId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', data.message);
            }
        });
    }
}

function rejectSavings(savingsId) {
    if (confirm('Reject this savings account?')) {
        fetch(`/approval-center/savings/${savingsId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', data.message);
            }
        });
    }
}

function approveKyc(kycId) {
    if (confirm('Verify this KYC document?')) {
        fetch(`/approval-center/kyc/${kycId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', data.message);
            }
        });
    }
}

function rejectKyc(kycId) {
    const reason = prompt('Reason for rejection:');
    if (reason) {
        fetch(`/approval-center/kyc/${kycId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', data.message);
            }
        });
    }
}

function approveCollateral(collateralId) {
    if (confirm('Verify this collateral?')) {
        fetch(`/approval-center/collateral/${collateralId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', data.message);
            }
        });
    }
}

function rejectCollateral(collateralId) {
    if (confirm('Reject this collateral?')) {
        fetch(`/approval-center/collateral/${collateralId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', data.message);
            }
        });
    }
}

function approveClient(clientId) {
    if (confirm('Approve this client?')) {
        fetch(`/approval-center/clients/${clientId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', data.message);
            }
        });
    }
}

function rejectClient(clientId) {
    if (confirm('Reject this client?')) {
        fetch(`/approval-center/clients/${clientId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', data.message);
            }
        });
    }
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${iconClass} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertAdjacentHTML('afterbegin', alertHtml);
}

// Auto-refresh every 15 seconds
setInterval(refreshApprovals, 15000);
</script>
@endpush
@endsection

