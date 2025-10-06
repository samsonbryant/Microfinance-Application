@extends('layouts.app')

@section('title', 'Recovery Actions')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-tools me-2"></i>Recovery Actions</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newRecoveryModal">
                    <i class="fas fa-plus me-2"></i>New Recovery Action
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>Recovery Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Collection Case</th>
                                    <th>Action Type</th>
                                    <th>Notes</th>
                                    <th>Performed By</th>
                                    <th>Status</th>
                                    <th>Next Action</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No recovery actions found</p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newRecoveryModal">
                                            Create First Recovery Action
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

<!-- New Recovery Action Modal -->
<div class="modal fade" id="newRecoveryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Recovery Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="collection_case" class="form-label">Collection Case</label>
                                <select class="form-select" id="collection_case">
                                    <option value="">Select Collection Case</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="action_type" class="form-label">Action Type</label>
                                <select class="form-select" id="action_type">
                                    <option value="phone_call">Phone Call</option>
                                    <option value="email">Email</option>
                                    <option value="visit">In-Person Visit</option>
                                    <option value="legal_notice">Legal Notice</option>
                                    <option value="collateral_seizure">Collateral Seizure</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" rows="4" placeholder="Enter action details..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="action_date" class="form-label">Action Date</label>
                                <input type="datetime-local" class="form-control" id="action_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="next_action_date" class="form-label">Next Action Date</label>
                                <input type="date" class="form-control" id="next_action_date">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Create Recovery Action</button>
            </div>
        </div>
    </div>
</div>
@endsection
