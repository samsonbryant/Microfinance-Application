@extends('layouts.app')

@section('title', 'Create Recovery Action')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-gavel text-danger me-2"></i>Create Recovery Action
            </h1>
            <p class="text-muted mb-0">Initiate recovery action for overdue loan</p>
        </div>
        <a href="{{ route('recovery-actions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <form action="{{ route('recovery-actions.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">Recovery Action Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="loan_id" class="form-label">Overdue Loan <span class="text-danger">*</span></label>
                                <select class="form-select @error('loan_id') is-invalid @enderror" id="loan_id" name="loan_id" required>
                                    <option value="">Select Loan</option>
                                    @foreach($loans as $loan)
                                        <option value="{{ $loan->id }}">
                                            {{ $loan->loan_number }} - {{ $loan->client->full_name }} - ${{ number_format($loan->outstanding_balance, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('loan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="action_type" class="form-label">Action Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('action_type') is-invalid @enderror" id="action_type" name="action_type" required>
                                    <option value="">Select Action Type</option>
                                    <option value="phone_call">Phone Call</option>
                                    <option value="visit">Field Visit</option>
                                    <option value="email">Email Notification</option>
                                    <option value="sms">SMS Reminder</option>
                                    <option value="letter">Formal Letter</option>
                                    <option value="legal_action">Legal Action</option>
                                    <option value="escalation">Escalation</option>
                                </select>
                                @error('action_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="action_date" class="form-label">Action Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('action_date') is-invalid @enderror" 
                                       id="action_date" name="action_date" value="{{ old('action_date', now()->format('Y-m-d\TH:i')) }}" required>
                                @error('action_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                                @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Action Description <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="expected_outcome" class="form-label">Expected Outcome</label>
                                <textarea class="form-control" id="expected_outcome" name="expected_outcome" rows="2">{{ old('expected_outcome') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('recovery-actions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-save me-1"></i>Create Action
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h6 class="m-0 font-weight-bold">Recovery Guidelines</h6>
                    </div>
                    <div class="card-body">
                        <h6 class="text-primary">Action Types:</h6>
                        <ul class="small">
                            <li><strong>Phone Call:</strong> Initial contact attempt</li>
                            <li><strong>Field Visit:</strong> Physical visit to client</li>
                            <li><strong>Email:</strong> Formal email notification</li>
                            <li><strong>SMS:</strong> Text message reminder</li>
                            <li><strong>Formal Letter:</strong> Written demand notice</li>
                            <li><strong>Legal Action:</strong> Legal proceedings</li>
                            <li><strong>Escalation:</strong> Escalate to higher authority</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

