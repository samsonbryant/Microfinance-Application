<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Real-time Status Bar -->
    <div class="alert alert-info d-flex align-items-center justify-content-between mb-4">
        <div>
            <i class="fas fa-circle text-success me-2 pulse-animation"></i>
            <strong>Live Collections Data:</strong> Last updated <span id="last-update-collections">just now</span>
        </div>
        <button type="button" class="btn btn-sm btn-light" wire:click="$refresh">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Due Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['due_today_count'] }}
                            </div>
                            <small class="text-muted">${{ number_format($stats['due_today_amount'], 2) }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Overdue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['overdue_count'] }}
                            </div>
                            <small class="text-muted">${{ number_format($stats['overdue_amount'], 2) }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Loans
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['active_count'] }}
                            </div>
                            <small class="text-muted">${{ number_format($stats['active_amount'], 2) }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Upcoming (30 Days)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['upcoming_count'] }}
                            </div>
                            <small class="text-muted">${{ number_format($stats['upcoming_amount'], 2) }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs for different views -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $selectedTab === 'due-today' ? 'active' : '' }}" 
                    wire:click="selectTab('due-today')" type="button">
                <i class="fas fa-clock me-1"></i>Due Today 
                <span class="badge bg-warning">{{ $stats['due_today_count'] }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $selectedTab === 'overdue' ? 'active' : '' }}" 
                    wire:click="selectTab('overdue')" type="button">
                <i class="fas fa-exclamation-triangle me-1"></i>Overdue 
                <span class="badge bg-danger">{{ $stats['overdue_count'] }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $selectedTab === 'upcoming' ? 'active' : '' }}" 
                    wire:click="selectTab('upcoming')" type="button">
                <i class="fas fa-calendar-alt me-1"></i>Upcoming 
                <span class="badge bg-info">{{ $stats['upcoming_count'] }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $selectedTab === 'all-active' ? 'active' : '' }}" 
                    wire:click="selectTab('all-active')" type="button">
                <i class="fas fa-list me-1"></i>All Active
            </button>
        </li>
    </ul>

    <!-- Loans Table -->
    <div class="card shadow">
        <div class="card-header 
            {{ $selectedTab === 'due-today' ? 'bg-warning text-white' : '' }}
            {{ $selectedTab === 'overdue' ? 'bg-danger text-white' : '' }}
            {{ $selectedTab === 'upcoming' ? 'bg-info text-white' : '' }}
            {{ $selectedTab === 'all-active' ? 'bg-primary text-white' : '' }}">
            <h6 class="m-0 font-weight-bold">
                @if($selectedTab === 'due-today')
                    <i class="fas fa-clock me-2"></i>Loans Due Today
                @elseif($selectedTab === 'overdue')
                    <i class="fas fa-exclamation-triangle me-2"></i>Overdue Loans - Action Required
                @elseif($selectedTab === 'upcoming')
                    <i class="fas fa-calendar-alt me-2"></i>Upcoming Payments (Next 30 Days)
                @else
                    <i class="fas fa-list me-2"></i>All Active Loans
                @endif
            </h6>
        </div>
        <div class="card-body">
            @if($loans && (is_countable($loans) ? count($loans) : $loans->count()) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Loan #</th>
                                <th>Client</th>
                                <th>Contact</th>
                                <th>Outstanding</th>
                                <th>Next Payment</th>
                                <th>Due Date</th>
                                @if($selectedTab === 'overdue')
                                    <th>Days Overdue</th>
                                @endif
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loans as $loan)
                            <tr>
                                <td><strong>{{ $loan->loan_number }}</strong></td>
                                <td>
                                    {{ $loan->client->full_name ?? $loan->client->first_name . ' ' . $loan->client->last_name }}
                                </td>
                                <td>
                                    <small class="text-muted">{{ $loan->client->phone ?? 'N/A' }}</small>
                                </td>
                                <td><strong class="text-danger">${{ number_format($loan->outstanding_balance ?? 0, 2) }}</strong></td>
                                <td><strong class="text-success">${{ number_format($loan->next_payment_amount ?? 0, 2) }}</strong></td>
                                <td>{{ $loan->next_due_date ? $loan->next_due_date->format('M d, Y') : 'N/A' }}</td>
                                @if($selectedTab === 'overdue')
                                    <td>
                                        <span class="badge bg-danger">
                                            {{ $loan->next_due_date ? now()->diffInDays($loan->next_due_date) : 0 }} days
                                        </span>
                                    </td>
                                @endif
                                <td>
                                    @if($loan->status === 'overdue' || ($loan->next_due_date && $loan->next_due_date < now()))
                                        <span class="badge bg-danger">Overdue</span>
                                    @elseif($loan->next_due_date && $loan->next_due_date->isToday())
                                        <span class="badge bg-warning">Due Today</span>
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button wire:click="openPaymentModal({{ $loan->id }})" 
                                                class="btn btn-sm btn-success" title="Process Payment">
                                            <i class="fas fa-dollar-sign"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(method_exists($loans, 'links'))
                    <div class="mt-3">
                        {{ $loans->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>No Loans Found</h5>
                    <p class="text-muted">
                        @if($selectedTab === 'due-today')
                            No collections due today!
                        @elseif($selectedTab === 'overdue')
                            No overdue loans - Great job!
                        @elseif($selectedTab === 'upcoming')
                            No upcoming payments in the next 30 days.
                        @else
                            No active loans in your branch.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Payment Modal -->
    @if($showPaymentModal && $selectedLoan)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-dollar-sign me-2"></i>Process Payment
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closePaymentModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Loan Details -->
                        <div class="card mb-3 bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Loan Number:</strong> {{ $selectedLoan->loan_number }}</p>
                                        <p class="mb-1"><strong>Client:</strong> {{ $selectedLoan->client->full_name ?? $selectedLoan->client->first_name . ' ' . $selectedLoan->client->last_name }}</p>
                                        <p class="mb-0"><strong>Phone:</strong> {{ $selectedLoan->client->phone ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Outstanding Balance:</strong> <span class="text-danger">${{ number_format($selectedLoan->outstanding_balance, 2) }}</span></p>
                                        <p class="mb-1"><strong>Next Payment Amount:</strong> <span class="text-success">${{ number_format($selectedLoan->next_payment_amount ?? 0, 2) }}</span></p>
                                        <p class="mb-0"><strong>Due Date:</strong> {{ $selectedLoan->next_due_date ? $selectedLoan->next_due_date->format('M d, Y') : 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <form wire:submit.prevent="processPayment">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="paymentAmount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control @error('paymentAmount') is-invalid @enderror" 
                                               id="paymentAmount" wire:model="paymentAmount" step="0.01" required>
                                    </div>
                                    @error('paymentAmount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="paymentMethod" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                    <select class="form-select @error('paymentMethod') is-invalid @enderror" 
                                            id="paymentMethod" wire:model="paymentMethod" required>
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="mobile_money">Mobile Money</option>
                                        <option value="cheque">Cheque</option>
                                    </select>
                                    @error('paymentMethod') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="paymentDate" class="form-label">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('paymentDate') is-invalid @enderror" 
                                           id="paymentDate" wire:model="paymentDate" required>
                                    @error('paymentDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="referenceNumber" class="form-label">Reference Number</label>
                                    <input type="text" class="form-control @error('referenceNumber') is-invalid @enderror" 
                                           id="referenceNumber" wire:model="referenceNumber" placeholder="Optional">
                                    @error('referenceNumber') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" wire:model="notes" rows="2" placeholder="Optional notes"></textarea>
                                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="closePaymentModal">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-2"></i>Process Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .border-left-warning {
            border-left: 4px solid #f6c23e !important;
        }

        .border-left-danger {
            border-left: 4px solid #e74a3b !important;
        }

        .border-left-success {
            border-left: 4px solid #1cc88a !important;
        }

        .border-left-info {
            border-left: 4px solid #36b9cc !important;
        }
    </style>

    <script>
        // Auto-update timestamp
        setInterval(() => {
            const element = document.getElementById('last-update-collections');
            if (element) {
                element.textContent = new Date().toLocaleTimeString();
            }
        }, 1000);

        // Auto-refresh every 30 seconds
        setInterval(() => {
            @this.call('$refresh');
        }, 30000);
    </script>
</div>

