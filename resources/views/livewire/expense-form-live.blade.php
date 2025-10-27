<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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

            <!-- Expense Account -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-folder text-warning"></i> Expense Account <span class="text-danger">*</span>
                </label>
                <select wire:model="account_id" class="form-select @error('account_id') is-invalid @enderror">
                    <option value="">Select account...</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                    @endforeach
                </select>
                @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Amount -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-dollar-sign text-success"></i> Amount <span class="text-danger">*</span>
                </label>
                <input type="number" step="0.01" wire:model="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00">
                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Payment Method -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-credit-card text-info"></i> Payment Method <span class="text-danger">*</span>
                </label>
                <select wire:model="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                    <option value="cash">Cash</option>
                    <option value="cheque">Cheque</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="mobile_money">Mobile Money</option>
                </select>
                @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            @if($payment_method !== 'cash')
            <!-- Bank (if not cash) -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-university text-primary"></i> Bank <span class="text-danger">*</span>
                </label>
                <select wire:model="bank_id" class="form-select @error('bank_id') is-invalid @enderror">
                    <option value="">Select bank...</option>
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
                <input type="text" wire:model="reference_number" class="form-control @error('reference_number') is-invalid @enderror" placeholder="CHQ-1234 or TXN-5678">
                @error('reference_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            @endif

            <!-- Payee Name -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    <i class="fas fa-user text-primary"></i> Payee Name
                </label>
                <input type="text" wire:model="payee_name" class="form-control @error('payee_name') is-invalid @enderror" placeholder="Who received the payment?">
                @error('payee_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Description -->
            <div class="col-md-12">
                <label class="form-label fw-semibold">
                    <i class="fas fa-align-left text-info"></i> Description <span class="text-danger">*</span>
                </label>
                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Describe this expense..."></textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Submit Button -->
            <div class="col-md-12">
                <hr class="my-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="fas fa-save"></i> Create Expense
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
</div>

