@extends('layouts.app')

@section('title', 'Communication Logs')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-comments me-2"></i>Communication Logs</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCommunicationModal">
                    <i class="fas fa-plus me-2"></i>New Communication
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>Communication History
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Type</th>
                                    <th>Message</th>
                                    <th>Channel</th>
                                    <th>Status</th>
                                    <th>Sent By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No communication logs found</p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCommunicationModal">
                                            Create First Communication
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Communication Modal -->
<div class="modal fade" id="newCommunicationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Communication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="client" class="form-label">Client</label>
                                <select class="form-select" id="client">
                                    <option value="">Select Client</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-select" id="type">
                                    <option value="sms">SMS</option>
                                    <option value="email">Email</option>
                                    <option value="call">Phone Call</option>
                                    <option value="visit">In-Person Visit</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" rows="4" placeholder="Enter your message..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Send Communication</button>
            </div>
        </div>
    </div>
</div>
@endsection
