<div wire:poll.30s="refreshApplications" class="loan-officer-applications">
    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
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
        <div class="col-md-4">
            <div class="card border-start border-info border-4" style="border-radius: 8px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Under Review</small>
                            <h3 class="mb-0 fw-bold">{{ $underReviewCount }}</h3>
                        </div>
                        <i class="fas fa-search fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-success border-4" style="border-radius: 8px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Awaiting Approval</small>
                            <h3 class="mb-0 fw-bold">{{ $approvedCount }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-clipboard-list text-primary"></i> Loan Applications
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
                                <th>Client</th>
                                <th>Amount</th>
                                <th>Term</th>
                                <th>Applied</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applications as $loan)
                            <tr>
                                <td class="fw-bold">{{ $loan->loan_number }}</td>
                                <td>{{ $loan->client->full_name ?? 'N/A' }}</td>
                                <td>${{ number_format($loan->amount, 2) }}</td>
                                <td>{{ $loan->loan_term ?? $loan->term_months }} months</td>
                                <td>{{ $loan->application_date ? $loan->application_date->format('M d, Y') : $loan->created_at->format('M d, Y') }}</td>
                                <td>
                                    @php
                                        $statusConfig = [
                                            'pending' => ['class' => 'warning', 'icon' => 'clock'],
                                            'under_review' => ['class' => 'info', 'icon' => 'search'],
                                            'approved' => ['class' => 'success', 'icon' => 'check-circle'],
                                            'rejected' => ['class' => 'danger', 'icon' => 'times-circle'],
                                            'disbursed' => ['class' => 'primary', 'icon' => 'money-bill-wave'],
                                        ];
                                        $status = $statusConfig[$loan->status] ?? ['class' => 'secondary', 'icon' => 'question'];
                                    @endphp
                                    <span class="badge bg-{{ $status['class'] }}">
                                        <i class="fas fa-{{ $status['icon'] }}"></i> {{ ucfirst(str_replace('_', ' ', $loan->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        @if($loan->status === 'pending' && auth()->user()->hasRole('loan_officer'))
                                            <button wire:click="moveToReview({{ $loan->id }})" class="btn btn-sm btn-info">
                                                <i class="fas fa-search"></i> Review
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Applications</h5>
                    <p class="text-muted">New applications will appear here</p>
                </div>
            @endif
        </div>
    </div>

    <div class="text-center mt-3">
        <small class="text-muted">
            <i class="fas fa-sync-alt"></i> Updates every 30 seconds
        </small>
    </div>
</div>

