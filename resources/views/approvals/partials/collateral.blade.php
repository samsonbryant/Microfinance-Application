@if($items->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Value</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $collateral)
                <tr>
                    <td>
                        {{ $collateral->client->full_name ?? 'N/A' }}<br>
                        <small class="text-muted">{{ $collateral->client->phone }}</small>
                    </td>
                    <td><span class="badge bg-info">{{ ucfirst($collateral->type) }}</span></td>
                    <td>
                        <div class="text-truncate" style="max-width: 200px;" title="{{ $collateral->description }}">
                            {{ $collateral->description }}
                        </div>
                    </td>
                    <td><strong class="text-success">${{ number_format($collateral->value ?? 0, 2) }}</strong></td>
                    <td><small>{{ $collateral->created_at->diffForHumans() }}</small></td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('collaterals.show', $collateral) }}" class="btn btn-sm btn-info" title="View" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-success" onclick="approveCollateral({{ $collateral->id }})" title="Verify">
                                <i class="fas fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="rejectCollateral({{ $collateral->id }})" title="Reject">
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
        <i class="fas fa-check-circle me-2"></i>No pending collateral verifications.
    </div>
@endif

