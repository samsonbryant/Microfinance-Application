<div class="transfer-form-live">
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

            <!-- Transfer Type -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-tag text-info"></i> Transfer Type <span class="text-danger">*</span>
                </label>
                <select wire:model="type" class="form-select @error('type') is-invalid @enderror">
                    <option value="transfer">Transfer</option>
                    <option value="deposit">Deposit</option>
                    <option value="withdrawal">Withdrawal</option>
                    <option value="disbursement">Disbursement</option>
                    <option value="expense">Expense</option>
                </select>
                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- From Account -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-arrow-circle-left text-warning"></i> From Account <span class="text-danger">*</span>
                </label>
                <select wire:model="from_account_id" class="form-select @error('from_account_id') is-invalid @enderror">
                    <option value="">Select account...</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                    @endforeach
                </select>
                @error('from_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- To Account -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-arrow-circle-right text-success"></i> To Account <span class="text-danger">*</span>
                </label>
                <select wire:model="to_account_id" class="form-select @error('to_account_id') is-invalid @enderror">
                    <option value="">Select account...</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                    @endforeach
                </select>
                @error('to_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- From Bank (Optional) -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-university text-primary"></i> From Bank (Optional)
                </label>
                <select wire:model="from_bank_id" class="form-select @error('from_bank_id') is-invalid @enderror">
                    <option value="">Select bank...</option>
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                    @endforeach
                </select>
                @error('from_bank_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- To Bank (Optional) -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-university text-success"></i> To Bank (Optional)
                </label>
                <select wire:model="to_bank_id" class="form-select @error('to_bank_id') is-invalid @enderror">
                    <option value="">Select bank...</option>
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                    @endforeach
                </select>
                @error('to_bank_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Amount -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-dollar-sign text-success"></i> Amount <span class="text-danger">*</span>
                </label>
                <input type="number" step="0.01" wire:model="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00">
                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Describe this transfer..."></textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Submit Button -->
            <div class="col-md-12">
                <hr class="my-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-info" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="fas fa-exchange-alt"></i> Create Transfer
                        </span>
                        <span wire:loading>
                            <i class="fas fa-spinner fa-spin"></i> Processing...
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
    .transfer-form-live {
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

