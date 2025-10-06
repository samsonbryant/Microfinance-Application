<div>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">New Loan Application</h5>
        </div>
        <div class="card-body">
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form wire:submit.prevent="submit">
                <div class="row">
                    <!-- Client Selection -->
                    <div class="col-md-6 mb-3">
                        <label for="client_id" class="form-label">Client *</label>
                        <select wire:model="client_id" class="form-select @error('client_id') is-invalid @enderror" id="client_id">
                            <option value="">Select a client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->id_number }})</option>
                            @endforeach
                        </select>
                        @error('client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Loan Amount -->
                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">Loan Amount *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" wire:model="amount" class="form-control @error('amount') is-invalid @enderror" 
                                   id="amount" placeholder="Enter loan amount" step="0.01">
                        </div>
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Interest Rate -->
                    <div class="col-md-4 mb-3">
                        <label for="interest_rate" class="form-label">Interest Rate (%) *</label>
                        <div class="input-group">
                            <input type="number" wire:model="interest_rate" class="form-control @error('interest_rate') is-invalid @enderror" 
                                   id="interest_rate" placeholder="12" step="0.01" min="1" max="50">
                            <span class="input-group-text">%</span>
                        </div>
                        @error('interest_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Term in Months -->
                    <div class="col-md-4 mb-3">
                        <label for="term_months" class="form-label">Term (Months) *</label>
                        <input type="number" wire:model="term_months" class="form-control @error('term_months') is-invalid @enderror" 
                               id="term_months" placeholder="12" min="1" max="60">
                        @error('term_months') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Loan Type -->
                    <div class="col-md-4 mb-3">
                        <label for="loan_type" class="form-label">Loan Type *</label>
                        <select wire:model="loan_type" class="form-select @error('loan_type') is-invalid @enderror" id="loan_type">
                            <option value="">Select loan type</option>
                            <option value="personal">Personal</option>
                            <option value="business">Business</option>
                            <option value="agricultural">Agricultural</option>
                            <option value="emergency">Emergency</option>
                        </select>
                        @error('loan_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Purpose -->
                    <div class="col-12 mb-3">
                        <label for="purpose" class="form-label">Purpose *</label>
                        <textarea wire:model="purpose" class="form-control @error('purpose') is-invalid @enderror" 
                                  id="purpose" rows="3" placeholder="Describe the purpose of the loan"></textarea>
                        @error('purpose') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Collateral Section -->
                    @if($showCollateralForm)
                    <div class="col-12">
                        <h6 class="text-primary mb-3">Collateral Information</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="collateral_type" class="form-label">Collateral Type *</label>
                                <input type="text" wire:model="collateral_type" class="form-control @error('collateral_type') is-invalid @enderror" 
                                       id="collateral_type" placeholder="e.g., Land, Vehicle, Equipment">
                                @error('collateral_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="collateral_value" class="form-label">Collateral Value *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" wire:model="collateral_value" class="form-control @error('collateral_value') is-invalid @enderror" 
                                           id="collateral_value" placeholder="0.00" step="0.01">
                                </div>
                                @error('collateral_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">LTV Ratio</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-{{ $this->calculateLTV() > 80 ? 'danger' : ($this->calculateLTV() > 60 ? 'warning' : 'success') }}">
                                        {{ $this->calculateLTV() }}%
                                    </span>
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="collateral_description" class="form-label">Collateral Description</label>
                                <textarea wire:model="collateral_description" class="form-control @error('collateral_description') is-invalid @enderror" 
                                          id="collateral_description" rows="2" placeholder="Describe the collateral"></textarea>
                                @error('collateral_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Loan Summary -->
                    @if($amount && $interest_rate && $term_months)
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Loan Summary</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Principal:</strong> ${{ number_format($amount, 2) }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Interest Rate:</strong> {{ $interest_rate }}%
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Term:</strong> {{ $term_months }} months
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Monthly Payment:</strong> ${{ number_format($this->calculateMonthlyPayment(), 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" wire:click="resetForm" class="btn btn-secondary">
                        Reset
                    </button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="submit">
                        <span wire:loading.remove wire:target="submit">Submit Application</span>
                        <span wire:loading wire:target="submit">
                            <i class="fas fa-spinner fa-spin"></i> Submitting...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>