@if($items->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Loan #</th>
                    <th>Client</th>
                    <th>Amount</th>
                    <th>Term</th>
                    <th>Interest</th>
                    <th>Purpose</th>
                    <th>Applied</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $loan)
                <tr>
                    <td><strong>{{ $loan->loan_number }}</strong></td>
                    <td>
                        {{ $loan->client->full_name ?? 'N/A' }}<br>
                        <small class="text-muted">{{ $loan->client->phone }}</small>
                    </td>
                    <td><strong class="text-primary">${{ number_format($loan->amount, 2) }}</strong></td>
                    <td>{{ $loan->term_months }} months</td>
                    <td>{{ $loan->interest_rate }}%</td>
                    <td>
                        <div class="text-truncate" style="max-width: 150px;" title="{{ $loan->loan_purpose }}">
                            {{ $loan->loan_purpose }}
                        </div>
                    </td>
                    <td><small>{{ $loan->created_at->diffForHumans() }}</small></td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-info" title="View" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-success" onclick="approveLoan({{ $loan->id }})" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="rejectLoan({{ $loan->id }})" title="Reject">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>No pending loan approvals.
    </div>
@endif

