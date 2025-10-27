<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Application Form -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>New Loan Application
                    </h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="submit">
                        <!-- Loan Amount -->
                        <div class="mb-3">
                            <label for="amount" class="form-label">Loan Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" wire:model.live="amount" step="0.01" min="100" required>
                            </div>
                            @error('amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            <small class="text-muted">Minimum: $100 | Maximum: $1,000,000</small>
                        </div>

                        <div class="row">
                            <!-- Interest Rate -->
                            <div class="col-md-6 mb-3">
                                <label for="interest_rate" class="form-label">Interest Rate (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('interest_rate') is-invalid @enderror" 
                                           id="interest_rate" wire:model.live="interest_rate" step="0.1" min="0" max="100" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('interest_rate') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <!-- Term -->
                            <div class="col-md-6 mb-3">
                                <label for="term_months" class="form-label">Loan Term (Months) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('term_months') is-invalid @enderror" 
                                       id="term_months" wire:model.live="term_months" min="1" max="360" required>
                                @error('term_months') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                <small class="text-muted">1 to 360 months</small>
                            </div>
                        </div>

                        <!-- Purpose -->
                        <div class="mb-3">
                            <label for="purpose" class="form-label">Loan Purpose <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('purpose') is-invalid @enderror" 
                                      id="purpose" wire:model="purpose" rows="3" required 
                                      placeholder="Describe the purpose of this loan..."></textarea>
                            @error('purpose') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <!-- Employment Status -->
                            <div class="col-md-6 mb-3">
                                <label for="employment_status" class="form-label">Employment Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('employment_status') is-invalid @enderror" 
                                        id="employment_status" wire:model="employment_status" required>
                                    <option value="">Select Status</option>
                                    <option value="employed">Employed</option>
                                    <option value="self_employed">Self Employed</option>
                                    <option value="unemployed">Unemployed</option>
                                    <option value="retired">Retired</option>
                                </select>
                                @error('employment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Monthly Income -->
                            <div class="col-md-6 mb-3">
                                <label for="monthly_income" class="form-label">Monthly Income <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('monthly_income') is-invalid @enderror" 
                                           id="monthly_income" wire:model="monthly_income" step="0.01" min="0" required>
                                </div>
                                @error('monthly_income') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Existing Loans -->
                        <div class="mb-3">
                            <label for="existing_loans" class="form-label">Do you have existing loans? <span class="text-danger">*</span></label>
                            <select class="form-select @error('existing_loans') is-invalid @enderror" 
                                    id="existing_loans" wire:model="existing_loans" required>
                                <option value="no">No</option>
                                <option value="yes">Yes</option>
                            </select>
                            @error('existing_loans') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Collateral Description -->
                        <div class="mb-3">
                            <label for="collateral_description" class="form-label">Collateral Description (Optional)</label>
                            <textarea class="form-control @error('collateral_description') is-invalid @enderror" 
                                      id="collateral_description" wire:model="collateral_description" rows="2" 
                                      placeholder="Describe any collateral you can provide..."></textarea>
                            @error('collateral_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Property, vehicle, equipment, etc.</small>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2 mt-4">
                            <button type="button" wire:click="preview" class="btn btn-info">
                                <i class="fas fa-eye me-2"></i>Preview Calculation
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane me-2"></i>Submit Application
                            </button>
                            <a href="{{ route('borrower.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Live Calculation Preview -->
        <div class="col-lg-4">
            <div class="card shadow sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>Loan Calculation
                        <i class="fas fa-circle text-success pulse-animation" style="font-size: 0.5rem; float: right; margin-top: 7px;"></i>
                    </h6>
                </div>
                <div class="card-body">
                    @if($showPreview && $amount > 0)
                        <div class="calculation-item">
                            <span class="calc-label">Principal Amount:</span>
                            <span class="calc-value">${{ number_format($amount, 2) }}</span>
                        </div>
                        <div class="calculation-item">
                            <span class="calc-label">Interest Rate:</span>
                            <span class="calc-value">{{ number_format($interest_rate, 2) }}%</span>
                        </div>
                        <div class="calculation-item">
                            <span class="calc-label">Loan Term:</span>
                            <span class="calc-value">{{ $term_months }} months</span>
                        </div>
                        <hr>
                        <div class="calculation-item highlight">
                            <span class="calc-label"><strong>Interest Amount:</strong></span>
                            <span class="calc-value text-info"><strong>${{ number_format($calculated_interest, 2) }}</strong></span>
                        </div>
                        <div class="calculation-item highlight">
                            <span class="calc-label"><strong>Total Amount:</strong></span>
                            <span class="calc-value text-primary"><strong>${{ number_format($calculated_total, 2) }}</strong></span>
                        </div>
                        <hr>
                        <div class="calculation-item monthly-payment">
                            <span class="calc-label"><strong>Monthly Payment:</strong></span>
                            <span class="calc-value text-success"><strong>${{ number_format($calculated_monthly, 2) }}</strong></span>
                        </div>
                        
                        <div class="alert alert-info mt-3 mb-0">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Interest is calculated as <strong>{{ $interest_rate }}%</strong> of your principal amount.
                            </small>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calculator fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Enter loan details to see calculation</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Application Guide -->
            <div class="card shadow mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Application Process
                    </h6>
                </div>
                <div class="card-body">
                    <div class="workflow-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <strong>You Submit</strong>
                            <small>Fill and submit application</small>
                        </div>
                    </div>
                    <div class="workflow-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <strong>Loan Officer Reviews</strong>
                            <small>Adds required documents</small>
                        </div>
                    </div>
                    <div class="workflow-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <strong>Branch Manager</strong>
                            <small>Reviews KYC documents</small>
                        </div>
                    </div>
                    <div class="workflow-step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <strong>Admin Approves</strong>
                            <small>Final approval & disbursement</small>
                        </div>
                    </div>
                    <div class="alert alert-success mt-3 mb-0">
                        <small><i class="fas fa-bell me-1"></i>You'll get real-time updates at each step!</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .calculation-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .calculation-item.highlight {
            background: #f8f9fa;
            padding: 0.75rem;
            margin: 0 -1rem;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .calculation-item.monthly-payment {
            background: #e7f5ff;
            padding: 1rem;
            margin: 0 -1rem;
            padding-left: 1rem;
            padding-right: 1rem;
            border-radius: 5px;
            border: none;
        }
        
        .calc-label {
            color: #6c757d;
        }
        
        .calc-value {
            font-weight: 600;
        }

        .workflow-step {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .workflow-step:last-child {
            border-bottom: none;
        }

        .step-number {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .step-content {
            flex: 1;
        }

        .step-content strong {
            display: block;
            margin-bottom: 0.25rem;
        }

        .step-content small {
            color: #6c757d;
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
    </style>
</div>

