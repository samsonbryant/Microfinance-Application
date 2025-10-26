<div wire:poll.30s="refreshApplications" class="loan-application-status">
    <!-- Status Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-start border-warning border-4" style="border-radius: 8px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Pending Review</small>
                            <h3 class="mb-0 fw-bold">{{ $pendingCount }}</h3>
                        </div>
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-4" style="border-radius: 8px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Approved</small>
                            <h3 class="mb-0 fw-bold">{{ $approvedCount }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-primary border-4" style="border-radius: 8px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Disbursed</small>
                            <h3 class="mb-0 fw-bold">{{ $disbursedCount }}</h3>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-danger border-4" style="border-radius: 8px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Rejected</small>
                            <h3 class="mb-0 fw-bold">{{ $rejectedCount }}</h3>
                        </div>
                        <i class="fas fa-times-circle fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications List with Real-time Updates -->
    <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-file-alt text-primary"></i> My Loan Applications
            </h5>
            <div wire:loading class="text-muted">
                <i class="fas fa-sync fa-spin"></i> Updating...
            </div>
        </div>
        <div class="card-body">
            @if($applications->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Loan #</th>
                                <th>Amount</th>
                                <th>Purpose</th>
                                <th>Applied Date</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applications as $loan)
                            <tr>
                                <td class="fw-bold">{{ $loan->loan_number }}</td>
                                <td>${{ number_format($loan->amount, 2) }}</td>
                                <td class="small">{{ \Str::limit($loan->loan_purpose ?? 'N/A', 30) }}</td>
                                <td>{{ $loan->application_date ? $loan->application_date->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    @php
                                        $statusConfig = [
                                            'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Pending Review'],
                                            'under_review' => ['class' => 'info', 'icon' => 'search', 'text' => 'Under Review'],
                                            'approved' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Approved'],
                                            'rejected' => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Rejected'],
                                            'disbursed' => ['class' => 'primary', 'icon' => 'money-bill-wave', 'text' => 'Disbursed'],
                                            'active' => ['class' => 'success', 'icon' => 'heartbeat', 'text' => 'Active'],
                                        ];
                                        $status = $statusConfig[$loan->status] ?? ['class' => 'secondary', 'icon' => 'question', 'text' => ucfirst($loan->status)];
                                    @endphp
                                    <span class="badge bg-{{ $status['class'] }}">
                                        <i class="fas fa-{{ $status['icon'] }}"></i> {{ $status['text'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 8px;">
                                        @php
                                            $progress = match($loan->status) {
                                                'pending' => 25,
                                                'under_review' => 50,
                                                'approved' => 75,
                                                'disbursed', 'active' => 100,
                                                'rejected' => 0,
                                                default => 0
                                            };
                                        @endphp
                                        <div class="progress-bar bg-{{ $status['class'] }}" style="width: {{ $progress }}%"></div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('borrower.loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Applications Yet</h5>
                    <p class="text-muted">Apply for your first loan to get started!</p>
                    <a href="{{ route('borrower.loans.create') }}" class="btn btn-primary" style="border-radius: 8px;">
                        <i class="fas fa-plus"></i> Apply for Loan
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Auto-refresh indicator -->
    <div class="text-center mt-3">
        <small class="text-muted">
            <i class="fas fa-sync-alt"></i> Updates automatically every 30 seconds
        </small>
    </div>
</div>

