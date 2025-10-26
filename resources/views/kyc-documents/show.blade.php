@extends('layouts.app')

@section('title', 'KYC Document Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-alt text-primary me-2"></i>KYC Document Details
            </h1>
            <p class="text-muted mb-0">Document #{{ $kycDocument->id }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('kyc-documents.download', $kycDocument) }}" class="btn btn-secondary">
                <i class="fas fa-download me-1"></i>Download
            </a>
            <a href="{{ route('kyc-documents.edit', $kycDocument) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            <a href="{{ route('kyc-documents.index') }}" class="btn btn-info">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <!-- Document Preview -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Document Preview</h6>
                </div>
                <div class="card-body text-center">
                    @if($kycDocument->is_image)
                        <img src="{{ $kycDocument->public_url }}" alt="Document" class="img-fluid" style="max-height: 600px;">
                    @elseif($kycDocument->is_pdf)
                        <iframe src="{{ $kycDocument->public_url }}" width="100%" height="600px" frameborder="0"></iframe>
                    @else
                        <div class="py-5">
                            <i class="fas fa-file fa-4x text-muted mb-3"></i>
                            <p class="text-muted">Preview not available for this file type</p>
                            <a href="{{ route('kyc-documents.download', $kycDocument) }}" class="btn btn-primary">
                                <i class="fas fa-download me-1"></i>Download to View
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Document Information -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Document Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Document Type:</th>
                            <td><span class="badge bg-info">{{ $kycDocument->document_type_name }}</span></td>
                        </tr>
                        <tr>
                            <th>Document Number:</th>
                            <td>{{ $kycDocument->document_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Issuing Authority:</th>
                            <td>{{ $kycDocument->issuing_authority ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Issue Date:</th>
                            <td>{{ $kycDocument->issue_date ? $kycDocument->issue_date->format('M d, Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Expiry Date:</th>
                            <td>
                                @if($kycDocument->expiry_date)
                                    <span class="{{ $kycDocument->isExpired() ? 'text-danger' : ($kycDocument->isExpiringSoon() ? 'text-warning' : '') }}">
                                        {{ $kycDocument->expiry_date->format('M d, Y') }}
                                        @if($kycDocument->isExpired())
                                            <span class="badge bg-danger ms-2">EXPIRED</span>
                                        @elseif($kycDocument->isExpiringSoon())
                                            <span class="badge bg-warning ms-2">EXPIRING SOON</span>
                                        @endif
                                    </span>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>File Name:</th>
                            <td>{{ $kycDocument->original_filename }}</td>
                        </tr>
                        <tr>
                            <th>File Size:</th>
                            <td>{{ $kycDocument->formatted_file_size }}</td>
                        </tr>
                        <tr>
                            <th>Notes:</th>
                            <td>{{ $kycDocument->notes ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Verification Section -->
            @if($kycDocument->verification_status === 'pending')
            <div class="card shadow mb-4 border-warning">
                <div class="card-header bg-warning">
                    <h6 class="m-0 font-weight-bold">Verification Required</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('kyc-documents.verify', $kycDocument) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="verification_notes" class="form-label">Verification Notes</label>
                            <textarea class="form-control" id="verification_notes" name="verification_notes" rows="3"></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="action" value="verify" class="btn btn-success">
                                <i class="fas fa-check me-1"></i>Verify Document
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger">
                                <i class="fas fa-times me-1"></i>Reject Document
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @elseif($kycDocument->verification_status === 'verified')
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Document Verified</strong>
                @if($kycDocument->verified_at)
                    <br><small>Verified on {{ $kycDocument->verified_at->format('M d, Y H:i') }}</small>
                @endif
            </div>
            @else
            <div class="alert alert-danger">
                <i class="fas fa-times-circle me-2"></i>
                <strong>Document Rejected</strong>
                @if($kycDocument->verification_notes)
                    <br><small>Reason: {{ $kycDocument->verification_notes }}</small>
                @endif
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Client Information -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Client Information</h6>
                </div>
                <div class="card-body">
                    @if($kycDocument->client)
                        <p><strong>Name:</strong><br>{{ $kycDocument->client->full_name }}</p>
                        <p><strong>Client Number:</strong><br>{{ $kycDocument->client->client_number }}</p>
                        <p><strong>Phone:</strong><br>{{ $kycDocument->client->phone }}</p>
                        <p><strong>Email:</strong><br>{{ $kycDocument->client->email ?? 'N/A' }}</p>
                        <a href="{{ route('clients.show', $kycDocument->client) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-user me-1"></i>View Client
                        </a>
                    @endif
                </div>
            </div>

            <!-- Upload Information -->
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="m-0 font-weight-bold">Upload Details</h6>
                </div>
                <div class="card-body">
                    <p><strong>Uploaded By:</strong><br>{{ $kycDocument->uploadedBy->name ?? 'N/A' }}</p>
                    <p><strong>Upload Date:</strong><br>{{ $kycDocument->created_at->format('M d, Y H:i') }}</p>
                    @if($kycDocument->verifiedBy)
                        <p><strong>Verified By:</strong><br>{{ $kycDocument->verifiedBy->name }}</p>
                    @endif
                    <p><strong>Last Updated:</strong><br>{{ $kycDocument->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

