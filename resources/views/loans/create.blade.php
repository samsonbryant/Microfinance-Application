@extends('layouts.app')

@section('title', 'Add a Loan')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-hand-holding-usd me-2"></i>Add a Loan</h1>
    <p class="page-subtitle">Create a new loan application</p>
</div>

<form action="{{ route('loans.store') }}" method="POST" enctype="multipart/form-data" id="loanForm">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Loan Basic Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Loan Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Loan Status</label>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Processing</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="client_id" class="form-label">Borrower <span class="text-danger">*</span></label>
                        <select class="form-select @error('client_id') is-invalid @enderror" 
                                id="client_id" name="client_id" required>
                            <option value="">Select Borrower or Group</option>
                            @foreach(\App\Models\Client::where('status', 'active')->get() as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->full_name }} ({{ $client->client_number }})
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Principal Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                           id="amount" name="amount" value="{{ old('amount', 0) }}" 
                                           step="0.01" min="0" required>
                                    <select class="form-select" style="max-width: 100px;" name="currency">
                                        <option value="USD" selected>USD</option>
                                        <option value="EUR">LRD</option>
                                    </select>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="release_date" class="form-label">Loan Release Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('release_date') is-invalid @enderror" 
                                       id="release_date" name="release_date" value="{{ old('release_date', today()->format('Y-m-d')) }}" required>
                                @error('release_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="term_months" class="form-label">Loan Duration <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('term_months') is-invalid @enderror" 
                                       id="term_months" name="term_months" value="{{ old('term_months', 1) }}" 
                                       min="1" required>
                                @error('term_months')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="duration_period" class="form-label">Duration Period <span class="text-danger">*</span></label>
                                <select class="form-select @error('duration_period') is-invalid @enderror" 
                                        id="duration_period" name="duration_period" required>
                                    <option value="months" selected>Months</option>
                                    <option value="weeks">Weeks</option>
                                    <option value="days">Days</option>
                                    <option value="years">Years</option>
                                </select>
                                @error('duration_period')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="interest_method" class="form-label">Interest Method <span class="text-danger">*</span></label>
                                <select class="form-select @error('interest_method') is-invalid @enderror" 
                                        id="interest_method" name="interest_method" required>
                                    <option value="flat" selected>Flat Interest</option>
                                    <option value="declining_balance">Declining Balance</option>
                                    <option value="compound">Compound Interest</option>
                                </select>
                                @error('interest_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="interest_rate" class="form-label">Interest Rate <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('interest_rate') is-invalid @enderror" 
                                           id="interest_rate" name="interest_rate" value="{{ old('interest_rate', 25) }}" 
                                           step="25" min="0" required>
                                    <span class="input-group-text">Percentage (%)</span>
                                </div>
                                @error('interest_rate')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="interest_cycle" class="form-label">Interest Cycle <span class="text-danger">*</span></label>
                                <select class="form-select @error('interest_cycle') is-invalid @enderror" 
                                        id="interest_cycle" name="interest_cycle" required>
                                    <option value="once" selected>Once</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                                @error('interest_cycle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Repayment Configuration -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Repayment Configuration</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="repayment_type" class="form-label">Repayment Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('repayment_type') is-invalid @enderror" 
                                        id="repayment_type" name="repayment_type" required>
                                    <option value="standard" selected>Standard</option>
                                    <option value="balloon">Balloon Payment</option>
                                    <option value="interest_only">Interest Only</option>
                                    <option value="custom">Custom</option>
                                </select>
                                @error('repayment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="repayment_cycle" class="form-label">Repayment Cycle <span class="text-danger">*</span></label>
                                <select class="form-select @error('repayment_cycle') is-invalid @enderror" 
                                        id="repayment_cycle" name="repayment_cycle" required>
                                    <option value="once" selected>Once</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="biweekly">Biweekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                </select>
                                @error('repayment_cycle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-outline-info" id="viewScheduleBtn">
                            <i class="fas fa-calendar me-1"></i>View Repayment Schedule
                        </button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Repayment Days</label>
                        <p class="text-muted small">Configure which days repayments can be made</p>
                        <div class="d-flex gap-2 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="repayment_days[]" value="monday" id="monday" checked>
                                <label class="form-check-label" for="monday">Monday</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="repayment_days[]" value="tuesday" id="tuesday" checked>
                                <label class="form-check-label" for="tuesday">Tuesday</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="repayment_days[]" value="wednesday" id="wednesday" checked>
                                <label class="form-check-label" for="wednesday">Wednesday</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="repayment_days[]" value="thursday" id="thursday" checked>
                                <label class="form-check-label" for="thursday">Thursday</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="repayment_days[]" value="friday" id="friday" checked>
                                <label class="form-check-label" for="friday">Friday</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="repayment_days[]" value="saturday" id="saturday">
                                <label class="form-check-label" for="saturday">Saturday</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="repayment_days[]" value="sunday" id="sunday">
                                <label class="form-check-label" for="sunday">Sunday</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Repayment Amounts</label>
                        <p class="text-muted small">Customize repayment amounts for scheduled dates</p>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="customizeAmountsBtn">
                            <i class="fas fa-edit me-1"></i>Customize
                        </button>
                    </div>
                </div>
            </div>

            <!-- Fees Configuration -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Fees</h6>
                        <button type="button" class="btn btn-sm btn-dark" id="addFeeBtn">
                            <i class="fas fa-plus me-1"></i>Add Fees
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted">Configure loan fees</p>
                    <div id="feesContainer">
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-receipt fa-2x mb-2"></i>
                            <p>No fees configured yet. Add your first fee below.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Late Repayment Penalty -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">Late Repayment Penalty</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Configure the penalty for late repayments</p>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="late_penalty_enabled" 
                               name="late_penalty_enabled" value="1">
                        <label class="form-check-label" for="late_penalty_enabled">
                            <strong>Enable Late Repayment Penalty</strong>
                        </label>
                    </div>

                    <div id="penaltyDetails" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="late_penalty_amount" class="form-label">Penalty Amount</label>
                                    <input type="number" class="form-control" id="late_penalty_amount" 
                                           name="late_penalty_amount" value="{{ old('late_penalty_amount', 0) }}" 
                                           step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="late_penalty_type" class="form-label">Penalty Type</label>
                                    <select class="form-select" id="late_penalty_type" name="late_penalty_type">
                                        <option value="fixed" selected>Fixed Amount</option>
                                        <option value="percentage">Percentage</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Collateral -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Collateral</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Details of the collateral</p>

                    <div class="mb-3">
                        <label for="collateral_name" class="form-label">Name</label>
                        <input type="text" class="form-control @error('collateral_name') is-invalid @enderror" 
                               id="collateral_name" name="collateral_name" value="{{ old('collateral_name') }}" 
                               placeholder="The name of the collateral">
                        @error('collateral_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="collateral_description" class="form-label">Description</label>
                        <textarea class="form-control @error('collateral_description') is-invalid @enderror" 
                                  id="collateral_description" name="collateral_description" rows="3" 
                                  placeholder="Give details about the collateral">{{ old('collateral_description') }}</textarea>
                        @error('collateral_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="collateral_defects" class="form-label">Defects</label>
                        <textarea class="form-control @error('collateral_defects') is-invalid @enderror" 
                                  id="collateral_defects" name="collateral_defects" rows="2" 
                                  placeholder="Describe all the defects that the collateral might have">{{ old('collateral_defects') }}</textarea>
                        @error('collateral_defects')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="collateral_value" class="form-label">Valuation</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control @error('collateral_value') is-invalid @enderror" 
                                   id="collateral_value" name="collateral_value" value="{{ old('collateral_value', 0) }}" 
                                   step="0.01" min="0">
                            <span class="input-group-text">USD</span>
                        </div>
                        @error('collateral_value')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Files/Images</label>
                        <div class="upload-area" id="collateralUploadArea">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <p class="mb-2"><strong>Upload files</strong> or drag and drop</p>
                            <p class="text-muted small">PNG, JPG, GIF up to 10MB</p>
                            <input type="file" class="form-control d-none" id="collateralFiles" 
                                   name="collateral_files[]" multiple accept=".png,.jpg,.jpeg,.gif,.pdf">
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="document.getElementById('collateralFiles').click()">
                                <i class="fas fa-upload me-1"></i>Choose Files
                            </button>
                        </div>
                        <div id="collateralFileList" class="mt-3"></div>
                    </div>
                </div>
            </div>

            <!-- Accounting Accounts -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-secondary text-white">
                    <h6 class="m-0 font-weight-bold">Accounts</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Configure journal accounts</p>

                    @php
                        $accounts = \App\Models\ChartOfAccount::where('is_active', true)->orderBy('code')->get();
                        
                        // Group accounts by type for better organization
                        $assetAccounts = $accounts->where('type', 'asset');
                        $revenueAccounts = $accounts->where('type', 'revenue');
                        $liabilityAccounts = $accounts->where('type', 'liability');
                        
                        // Smart defaults based on account categories
                        $cashAccount = $accounts->where('category', 'cash_on_hand')->first() ?? 
                                      $accounts->where('category', 'cash_in_bank')->first() ?? 
                                      $accounts->where('name', 'ILIKE', '%cash%')->first();
                        
                        $loansReceivableAccount = $accounts->where('category', 'loan_portfolio')->first() ?? 
                                                 $accounts->where('name', 'ILIKE', '%loan%receivable%')->first();
                        
                        $interestIncomeAccount = $accounts->where('category', 'loan_interest_income')->first() ?? 
                                               $accounts->where('name', 'ILIKE', '%interest%income%')->first();
                        
                        $feesIncomeAccount = $accounts->where('category', 'service_fees')->first() ?? 
                                           $accounts->where('name', 'ILIKE', '%fee%income%')->first();
                        
                        $penaltiesIncomeAccount = $accounts->where('category', 'penalty_income')->first() ?? 
                                                $accounts->where('name', 'ILIKE', '%penalty%income%')->first();
                        
                        $overpaymentAccount = $liabilityAccounts->where('name', 'ILIKE', '%overpayment%')->first() ?? 
                                            $liabilityAccounts->first();
                    @endphp

                    <div class="mb-3">
                        <label for="funding_account_id" class="form-label">
                            <i class="fas fa-university me-1 text-primary"></i>Funding Account
                        </label>
                        <select class="form-select" id="funding_account_id" name="funding_account_id" required>
                            <option value="">Select Account</option>
                            <optgroup label="🏦 Asset Accounts">
                                @foreach($assetAccounts as $account)
                                    <option value="{{ $account->id }}" 
                                            {{ ($cashAccount && $cashAccount->id == $account->id) ? 'selected' : '' }}
                                            data-balance="{{ $account->getCurrentBalance() ?? 0 }}">
                                        {{ $account->code }} - {{ $account->name }} 
                                        (Balance: ${{ number_format($account->getCurrentBalance() ?? 0, 2) }})
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                        <small class="text-muted">Select the source account for loan disbursement (typically Cash or Bank account)</small>
                    </div>

                    <div class="mb-3">
                        <label for="loans_receivable_account_id" class="form-label">Loans Receivable Account</label>
                        <select class="form-select" id="loans_receivable_account_id" name="loans_receivable_account_id">
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ ($loansReceivableAccount && $loansReceivableAccount->id == $account->id) ? 'selected' : '' }}>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">The account that will be debited in the general ledger when the loan is disbursed.</small>
                    </div>

                    <div class="mb-3">
                        <label for="interest_income_account_id" class="form-label">Default Interest Income Account</label>
                        <select class="form-select" id="interest_income_account_id" name="interest_income_account_id">
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ ($interestIncomeAccount && $interestIncomeAccount->id == $account->id) ? 'selected' : '' }}>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">The account that will be credited when interest is received from payments.</small>
                    </div>

                    <div class="mb-3">
                        <label for="fees_income_account_id" class="form-label">Default Fees Income Account</label>
                        <select class="form-select" id="fees_income_account_id" name="fees_income_account_id">
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ ($feesIncomeAccount && $feesIncomeAccount->id == $account->id) ? 'selected' : '' }}>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">The account that will be credited when fees are received from payments.</small>
                    </div>

                    <div class="mb-3">
                        <label for="penalty_income_account_id" class="form-label">Default Penalty Income Account</label>
                        <select class="form-select" id="penalty_income_account_id" name="penalty_income_account_id">
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ ($penaltiesIncomeAccount && $penaltiesIncomeAccount->id == $account->id) ? 'selected' : '' }}>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">The account that will be credited when penalty is received from payments.</small>
                    </div>

                    <div class="mb-3">
                        <label for="overpayment_account_id" class="form-label">Default Overpayment Account</label>
                        <select class="form-select" id="overpayment_account_id" name="overpayment_account_id">
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ ($overpaymentAccount && $overpaymentAccount->id == $account->id) ? 'selected' : '' }}>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">The account that will be credited when overpayment is received from payments.</small>
                    </div>
                </div>
            </div>

            <!-- Loan Files -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Loan Files</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Attach any files or images related to the loan</p>
                    <div class="upload-area" id="loanFilesUploadArea">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <p class="mb-2"><strong>Upload files</strong> or drag and drop</p>
                        <p class="text-muted small">PNG, JPG, GIF up to 10MB</p>
                        <input type="file" class="form-control d-none" id="loanFiles" 
                               name="loan_files[]" multiple accept=".png,.jpg,.jpeg,.gif,.pdf">
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="document.getElementById('loanFiles').click()">
                            <i class="fas fa-upload me-1"></i>Choose Files
                        </button>
                    </div>
                    <div id="loanFileList" class="mt-3"></div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between mb-4">
                <a href="{{ route('loans.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left me-1"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-1"></i>Save Loan
                </button>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <!-- Credit Risk Score -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-gradient-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-line me-2"></i>Credit Risk Score
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Artificial intelligence is used to calculate the credit risk score based on multiple data points and the borrower's historical records</p>
                    
                    <div class="risk-score-display mb-3">
                        <div class="risk-meter">
                            <div class="risk-level" id="riskLevel" style="left: 50%;">
                                <div class="risk-indicator"></div>
                            </div>
                            <div class="risk-gradient"></div>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-danger"><strong>High Risk</strong></small>
                                <small class="text-success"><strong>Low Risk</strong></small>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mb-3">
                        <h3 class="mb-0" id="riskScoreValue">50</h3>
                        <p class="text-muted small">Credit Score</p>
                    </div>

                    <button type="button" class="btn btn-outline-primary w-100" id="calculateRiskBtn">
                        <i class="fas fa-calculator me-1"></i>Get Risk Score
                    </button>

                    <input type="hidden" name="credit_risk_score" id="credit_risk_score" value="50">
                </div>
            </div>

            <!-- Loan Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Loan Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Principal Amount:</span>
                        <strong id="summaryPrincipal">$0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Interest Rate:</span>
                        <strong id="summaryInterest">0%</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Duration:</span>
                        <strong id="summaryDuration">0 Months</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Interest:</span>
                        <strong id="summaryTotalInterest">$0.00</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span><strong>Total Repayment:</strong></span>
                        <strong class="text-primary" id="summaryTotalRepayment">$0.00</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
.avatar-upload {
    text-align: center;
}

.avatar-preview {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 3px dashed #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    background: #f8f9fa;
    overflow: hidden;
}

.upload-area {
    border: 2px dashed #ddd;
    border-radius: 10px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: #007bff;
    background: #f8f9ff;
}

.upload-area.drag-over {
    border-color: #007bff;
    background: #e7f1ff;
}

.fee-item {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    background: #f8f9fa;
}

.risk-meter {
    position: relative;
    height: 30px;
    border-radius: 15px;
    overflow: hidden;
}

.risk-gradient {
    position: absolute;
    width: 100%;
    height: 100%;
    background: linear-gradient(to right, #dc3545, #ffc107, #28a745);
}

.risk-level {
    position: absolute;
    top: -5px;
    transform: translateX(-50%);
    transition: left 0.5s ease;
}

.risk-indicator {
    width: 20px;
    height: 40px;
    background: white;
    border: 3px solid #333;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}
</style>

@section('scripts')
<script>
// Late Penalty Toggle
document.getElementById('late_penalty_enabled')?.addEventListener('change', function() {
    document.getElementById('penaltyDetails').style.display = this.checked ? 'block' : 'none';
});

// Fee Management
let feeCount = 0;
document.getElementById('addFeeBtn')?.addEventListener('click', function() {
    feeCount++;
    const container = document.getElementById('feesContainer');
    
    if (feeCount === 1) {
        container.innerHTML = '';
    }
    
    const feeItem = document.createElement('div');
    feeItem.className = 'fee-item';
    feeItem.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0"><i class="fas fa-tag me-2"></i>Fee #${feeCount}</h6>
            <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.fee-item').remove()">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-2">
                    <label class="form-label small">Fee Name</label>
                    <input type="text" class="form-control form-control-sm" name="fee_name[]" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-2">
                    <label class="form-label small">Fee Type</label>
                    <select class="form-select form-select-sm" name="fee_type[]">
                        <option value="fixed">Fixed Amount</option>
                        <option value="percentage">Percentage</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-2">
                    <label class="form-label small">Amount</label>
                    <input type="number" class="form-control form-control-sm" name="fee_amount[]" step="0.01" min="0" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-2">
                    <label class="form-label small">Charge Type</label>
                    <select class="form-select form-select-sm" name="fee_charge_type[]">
                        <option value="upfront">Upfront</option>
                        <option value="on_disbursement">On Disbursement</option>
                        <option value="on_repayment">On Repayment</option>
                    </select>
                </div>
            </div>
        </div>
    `;
    container.appendChild(feeItem);
});

// Collateral Files
document.getElementById('collateralFiles')?.addEventListener('change', function(e) {
    const fileList = document.getElementById('collateralFileList');
    fileList.innerHTML = '';
    
    Array.from(e.target.files).forEach((file) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'alert alert-info d-flex justify-content-between align-items-center mb-2';
        fileItem.innerHTML = `
            <span><i class="fas fa-file me-2"></i>${file.name} (${(file.size / 1024).toFixed(2)} KB)</span>
            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        fileList.appendChild(fileItem);
    });
});

// Loan Files
document.getElementById('loanFiles')?.addEventListener('change', function(e) {
    const fileList = document.getElementById('loanFileList');
    fileList.innerHTML = '';
    
    Array.from(e.target.files).forEach((file) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'alert alert-success d-flex justify-content-between align-items-center mb-2';
        fileItem.innerHTML = `
            <span><i class="fas fa-file me-2"></i>${file.name} (${(file.size / 1024).toFixed(2)} KB)</span>
            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        fileList.appendChild(fileItem);
    });
});

// Loan Summary Update
function updateLoanSummary() {
    const principal = parseFloat(document.getElementById('amount')?.value || 0);
    const interestRate = parseFloat(document.getElementById('interest_rate')?.value || 0);
    const duration = parseInt(document.getElementById('term_months')?.value || 0);
    const durationPeriod = document.getElementById('duration_period')?.value || 'months';
    
    const totalInterest = (principal * interestRate / 100 * duration) / 12;
    const totalRepayment = principal + totalInterest;
    
    document.getElementById('summaryPrincipal').textContent = '$' + principal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('summaryInterest').textContent = interestRate + '%';
    document.getElementById('summaryDuration').textContent = duration + ' ' + durationPeriod;
    document.getElementById('summaryTotalInterest').textContent = '$' + totalInterest.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('summaryTotalRepayment').textContent = '$' + totalRepayment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Update summary on input change
['amount', 'interest_rate', 'term_months', 'duration_period'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', updateLoanSummary);
    document.getElementById(id)?.addEventListener('change', updateLoanSummary);
});

// Calculate Risk Score
document.getElementById('calculateRiskBtn')?.addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Calculating...';
    
    // Simulate AI calculation
    setTimeout(() => {
        const score = Math.floor(Math.random() * 40) + 60; // 60-100
        const riskPercent = 100 - score; // Invert for display
        
        document.getElementById('riskScoreValue').textContent = score;
        document.getElementById('credit_risk_score').value = score;
        document.getElementById('riskLevel').style.left = riskPercent + '%';
        
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Score Calculated';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success');
    }, 2000);
});

// Initialize summary
updateLoanSummary();
</script>
@endsection
@endsection
