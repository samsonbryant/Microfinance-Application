<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h6 class="m-0 font-weight-bold">All Pending Approvals</h6>
    </div>
    <div class="card-body">
        @if($pendingLoans->count() + $pendingSavings->count() + $pendingKycDocs->count() + $pendingCollateral->count() + $pendingClients->count() > 0)
            <!-- Pending Loans -->
            @if($pendingLoans->count() > 0)
                <h6 class="text-info mb-3"><i class="fas fa-hand-holding-usd me-2"></i>Pending Loans ({{ $pendingLoans->count() }})</h6>
                @include('approvals.partials.loans', ['items' => $pendingLoans])
                <hr>
            @endif

            <!-- Pending Savings -->
            @if($pendingSavings->count() > 0)
                <h6 class="text-success mb-3"><i class="fas fa-piggy-bank me-2"></i>Pending Savings Accounts ({{ $pendingSavings->count() }})</h6>
                @include('approvals.partials.savings', ['items' => $pendingSavings])
                <hr>
            @endif

            <!-- Pending KYC -->
            @if($pendingKycDocs->count() > 0)
                <h6 class="text-warning mb-3"><i class="fas fa-id-card me-2"></i>Pending KYC Documents ({{ $pendingKycDocs->count() }})</h6>
                @include('approvals.partials.kyc', ['items' => $pendingKycDocs])
                <hr>
            @endif

            <!-- Pending Collateral -->
            @if($pendingCollateral->count() > 0)
                <h6 class="text-danger mb-3"><i class="fas fa-shield-alt me-2"></i>Pending Collateral ({{ $pendingCollateral->count() }})</h6>
                @include('approvals.partials.collateral', ['items' => $pendingCollateral])
                <hr>
            @endif

            <!-- Pending Clients -->
            @if($pendingClients->count() > 0)
                <h6 class="text-secondary mb-3"><i class="fas fa-users me-2"></i>Pending Clients ({{ $pendingClients->count() }})</h6>
                @include('approvals.partials.clients', ['items' => $pendingClients])
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h5 class="text-success">All Clear!</h5>
                <p class="text-muted">No pending approvals at this time.</p>
            </div>
        @endif
    </div>
</div>

