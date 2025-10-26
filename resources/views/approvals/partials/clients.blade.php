@if($items->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Client #</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $client)
                <tr>
                    <td><strong>{{ $client->client_number }}</strong></td>
                    <td>{{ $client->full_name }}</td>
                    <td>{{ $client->phone }}</td>
                    <td>{{ $client->email ?? 'N/A' }}</td>
                    <td><small>{{ $client->created_at->diffForHumans() }}</small></td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-info" title="View" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-success" onclick="approveClient({{ $client->id }})" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="rejectClient({{ $client->id }})" title="Reject">
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
        <i class="fas fa-check-circle me-2"></i>No pending client approvals.
    </div>
@endif

