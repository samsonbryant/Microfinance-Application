@if($items->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Account #</th>
                    <th>Client</th>
                    <th>Account Type</th>
                    <th>Initial Deposit</th>
                    <th>Applied</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $savings)
                <tr>
                    <td><strong>{{ $savings->account_number }}</strong></td>
                    <td>
                        {{ $savings->client->full_name ?? 'N/A' }}<br>
                        <small class="text-muted">{{ $savings->client->phone }}</small>
                    </td>
                    <td><span class="badge bg-info">{{ ucfirst($savings->account_type ?? 'Savings') }}</span></td>
                    <td><strong class="text-success">${{ number_format($savings->balance ?? 0, 2) }}</strong></td>
                    <td><small>{{ $savings->created_at->diffForHumans() }}</small></td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('savings-accounts.show', $savings) }}" class="btn btn-sm btn-info" title="View" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-success" onclick="approveSavings({{ $savings->id }})" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="rejectSavings({{ $savings->id }})" title="Reject">
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
        <i class="fas fa-check-circle me-2"></i>No pending savings account approvals.
    </div>
@endif

