<div class="revenue-form-live">
    <form wire:submit.prevent="save">
        <div class="row g-3">
            <!-- Transaction Date -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-calendar text-primary"></i> Transaction Date <span class="text-danger">*</span>
                </label>
                <input type="date" wire:model="transaction_date" class="form-control @error('transaction_date') is-invalid @enderror">
                @error('transaction_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Revenue Type -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-tag text-success"></i> Revenue Type <span class="text-danger">*</span>
                </label>
                <select wire:model="revenue_type" class="form-select @error('revenue_type') is-invalid @enderror">
                    <option value="interest_received">Interest Received</option>
                    <option value="default_charges">Default Charges</option>
                    <option value="processing_fee">Processing Fee</option>
                    <option value="system_charge">System Charge</option>
                    <option value="other">Other</option>
                </select>
                @error('revenue_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Revenue Account -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-folder text-success"></i> Revenue Account <span class="text-danger">*</span>
                </label>
                <select wire:model="account_id" class="form-select @error('account_id') is-invalid @enderror">
                    <option value="">Select account...</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                    @endforeach
                </select>
                @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="text-muted">Account auto-suggests based on revenue type</small>
            </div>

            <!-- Amount -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-dollar-sign text-success"></i> Amount <span class="text-danger">*</span>
                </label>
                <input type="number" step="0.01" wire:model="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00">
                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Bank (Optional) -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-university text-primary"></i> Receiving Bank (Optional)
                </label>
                <select wire:model="bank_id" class="form-select @error('bank_id') is-invalid @enderror">
                    <option value="">Cash on Hand (default)</option>
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                    @endforeach
                </select>
                @error('bank_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Reference Number -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-hashtag text-secondary"></i> Reference Number
                </label>
                <input type="text" wire:model="reference_number" class="form-control @error('reference_number') is-invalid @enderror" placeholder="REF-1234">
                @error('reference_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Description -->
            <div class="col-md-12">
                <label class="form-label fw-semibold">
                    <i class="fas fa-align-left text-info"></i> Description <span class="text-danger">*</span>
                </label>
                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Describe this revenue..."></textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Revenue Type Guide -->
            <div class="col-md-12">
                <div class="alert alert-info">
                    <strong>Revenue Types:</strong>
                    <ul class="mb-0 small">
                        <li><strong>Interest Received:</strong> Interest earned from loans</li>
                        <li><strong>Default Charges:</strong> Late payment penalties</li>
                        <li><strong>Processing Fee:</strong> Loan origination fees</li>
                        <li><strong>System Charge:</strong> System maintenance fees</li>
                        <li><strong>Other:</strong> Miscellaneous income</li>
                    </ul>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="col-md-12">
                <hr class="my-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="fas fa-save"></i> Create Revenue Entry
                        </span>
                        <span wire:loading>
                            <i class="fas fa-spinner fa-spin"></i> Saving...
                        </span>
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </form>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
</div>

<style>
    .revenue-form-live {
        font-family: 'Inter', sans-serif;
    }
    
    .form-label {
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #E5E7EB;
        padding: 0.625rem 0.875rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #3B82F6;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.1);
    }
    
    .btn {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.625rem 1.25rem;
    }
</style>

