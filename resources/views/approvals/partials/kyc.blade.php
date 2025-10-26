@if($items->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Document Type</th>
                    <th>Document #</th>
                    <th>Expiry Date</th>
                    <th>Uploaded</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $kyc)
                <tr>
                    <td>
                        {{ $kyc->client->full_name ?? 'N/A' }}<br>
                        <small class="text-muted">{{ $kyc->client->client_number }}</small>
                    </td>
                    <td><span class="badge bg-info">{{ $kyc->document_type_name }}</span></td>
                    <td>{{ $kyc->document_number ?? 'N/A' }}</td>
                    <td>
                        @if($kyc->expiry_date)
                            <span class="{{ $kyc->isExpired() ? 'text-danger' : '' }}">
                                {{ $kyc->expiry_date->format('M d, Y') }}
                            </span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td><small>{{ $kyc->created_at->diffForHumans() }}</small></td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('kyc-documents.show', $kyc) }}" class="btn btn-sm btn-info" title="View" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('kyc-documents.download', $kyc) }}" class="btn btn-sm btn-secondary" title="Download" target="_blank">
                                <i class="fas fa-download"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-success" onclick="approveKyc({{ $kyc->id }})" title="Verify">
                                <i class="fas fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="rejectKyc({{ $kyc->id }})" title="Reject">
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
        <i class="fas fa-check-circle me-2"></i>No pending KYC document verifications.
    </div>
@endif

