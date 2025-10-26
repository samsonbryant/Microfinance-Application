@extends('layouts.app')

@section('title', 'Loan Repayment - ' . $loan->loan_number)

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-money-bill-wave text-success me-2"></i>Loan Repayment
            </h1>
            <p class="text-muted mb-0">Process payment for Loan #{{ $loan->loan_number }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('loans.show', $loan->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Loan
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Loan Information Panel -->
        <div class="col-xl-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Loan Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="loan-info">
                        <div class="info-item">
                            <label>Client:</label>
                            <div class="value">{{ $loan->client->first_name }} {{ $loan->client->last_name }}</div>
                        </div>
                        <div class="info-item">
                            <label>Loan Number:</label>
                            <div class="value">{{ $loan->loan_number }}</div>
                        </div>
                        <div class="info-item">
                            <label>Original Amount:</label>
                            <div class="value">${{ number_format($loan->amount, 2) }}</div>
                        </div>
                        <div class="info-item">
                            <label>Outstanding Balance:</label>
                            <div class="value text-warning">${{ number_format($loan->outstanding_balance, 2) }}</div>
                        </div>
                        <div class="info-item">
                            <label>Monthly Payment:</label>
                            <div class="value">${{ number_format($loan->monthly_payment ?? 0, 2) }}</div>
                        </div>
                        <div class="info-item">
                            <label>Next Due Date:</label>
                            <div class="value">{{ $loan->next_due_date ? $loan->next_due_date->format('M d, Y') : 'Not set' }}</div>
                        </div>
                        <div class="info-item">
                            <label>Status:</label>
                            <div class="value">
                                <span class="badge bg-{{ $loan->status === 'active' ? 'success' : ($loan->status === 'overdue' ? 'danger' : 'info') }}">
                                    {{ ucfirst($loan->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment History Preview -->
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Recent Payments
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                        @forelse($loan->transactions()->where('type', 'loan_repayment')->latest()->limit(5)->get() as $payment)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">${{ number_format($payment->amount, 2) }}</h6>
                                        <small class="text-muted">{{ $payment->created_at->format('M d, Y g:i A') }}</small>
                                    </div>
                                    <span class="badge bg-success">Paid</span>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-center text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p class="mb-0">No payments yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Repayment Form -->
        <div class="col-xl-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-credit-card me-2"></i>Process Payment
                    </h6>
                </div>
                <div class="card-body">
                    <form id="repaymentForm" action="{{ route('loans.process-repayment', $loan->id) }}" method="POST">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="payment_amount" class="form-label">
                                    <i class="fas fa-dollar-sign me-1"></i>Payment Amount *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control @error('payment_amount') is-invalid @enderror" 
                                           id="payment_amount" 
                                           name="payment_amount" 
                                           value="{{ old('payment_amount', $loan->monthly_payment ?? 0) }}" 
                                           step="0.01" 
                                           min="1" 
                                           max="{{ $loan->outstanding_balance }}"
                                           required
                                           onchange="calculatePaymentBreakdown()">
                                </div>
                                @error('payment_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Maximum: ${{ number_format($loan->outstanding_balance, 2) }}
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="payment_method" class="form-label">
                                    <i class="fas fa-credit-card me-1"></i>Payment Method *
                                </label>
                                <select class="form-select @error('payment_method') is-invalid @enderror" 
                                        id="payment_method" 
                                        name="payment_method" 
                                        required>
                                    <option value="">Select Method</option>
                                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="mobile_money" {{ old('payment_method') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                    <option value="check" {{ old('payment_method') === 'check' ? 'selected' : '' }}>Check</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="payment_date" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Payment Date *
                                </label>
                                <input type="date" 
                                       class="form-control @error('payment_date') is-invalid @enderror" 
                                       id="payment_date" 
                                       name="payment_date" 
                                       value="{{ old('payment_date', now()->format('Y-m-d')) }}" 
                                       required>
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="reference_number" class="form-label">
                                    <i class="fas fa-hashtag me-1"></i>Reference Number
                                </label>
                                <input type="text" 
                                       class="form-control @error('reference_number') is-invalid @enderror" 
                                       id="reference_number" 
                                       name="reference_number" 
                                       value="{{ old('reference_number') }}" 
                                       placeholder="Transaction/Receipt number">
                                @error('reference_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Payment Breakdown -->
                        <div class="card bg-light mb-4" id="payment-breakdown" style="display: none;">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-calculator me-2"></i>Payment Breakdown
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="breakdown-item">
                                            <div class="breakdown-value" id="principal-amount">$0.00</div>
                                            <div class="breakdown-label">Principal</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="breakdown-item">
                                            <div class="breakdown-value" id="interest-amount">$0.00</div>
                                            <div class="breakdown-label">Interest</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="breakdown-item">
                                            <div class="breakdown-value" id="penalty-amount">$0.00</div>
                                            <div class="breakdown-label">Penalty</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="breakdown-item">
                                            <div class="breakdown-value text-success" id="remaining-balance">$0.00</div>
                                            <div class="breakdown-label">Remaining Balance</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note me-1"></i>Notes
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Additional notes about this payment...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Quick Payment Buttons -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-zap me-1"></i>Quick Payment Options
                            </label>
                            <div class="btn-group d-flex" role="group">
                                <button type="button" class="btn btn-outline-primary" onclick="setPaymentAmount({{ $loan->monthly_payment ?? 0 }})">
                                    Monthly Payment<br>
                                    <small>${{ number_format($loan->monthly_payment ?? 0, 2) }}</small>
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="setPaymentAmount({{ $loan->outstanding_balance }})">
                                    Full Payment<br>
                                    <small>${{ number_format($loan->outstanding_balance, 2) }}</small>
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="setCustomAmount()">
                                    Custom Amount<br>
                                    <small>Enter manually</small>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('loans.show', $loan->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                <i class="fas fa-check me-1"></i>Process Payment
                                <span class="spinner-border spinner-border-sm ms-2 d-none" id="submitSpinner"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f1f1f1;
}

.info-item:last-child {
    border-bottom: none;
}

.info-item label {
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0;
}

.info-item .value {
    font-weight: 500;
    color: #495057;
}

.breakdown-item {
    padding: 1rem;
    text-align: center;
}

.breakdown-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #495057;
    margin-bottom: 0.5rem;
}

.breakdown-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-transform: uppercase;
    font-weight: 600;
}

.btn-group .btn small {
    font-size: 0.75rem;
    opacity: 0.8;
}

@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 0.5rem;
    }
}
</style>

<script>
function setPaymentAmount(amount) {
    document.getElementById('payment_amount').value = amount.toFixed(2);
    calculatePaymentBreakdown();
}

function setCustomAmount() {
    document.getElementById('payment_amount').focus();
}

function calculatePaymentBreakdown() {
    const amount = parseFloat(document.getElementById('payment_amount').value) || 0;
    const outstandingBalance = {{ $loan->outstanding_balance }};
    
    if (amount <= 0) {
        document.getElementById('payment-breakdown').style.display = 'none';
        return;
    }
    
    // Simple calculation - in real implementation, use proper amortization
    const monthlyPayment = {{ $loan->monthly_payment ?? 0 }};
    const interestRate = {{ $loan->interest_rate }} / 100 / 12;
    
    // Calculate interest portion
    const interestAmount = Math.min(amount, outstandingBalance * interestRate);
    const principalAmount = Math.max(0, amount - interestAmount);
    const penaltyAmount = 0; // Calculate based on overdue days
    const remainingBalance = Math.max(0, outstandingBalance - principalAmount);
    
    // Update breakdown display
    document.getElementById('principal-amount').textContent = '$' + principalAmount.toFixed(2);
    document.getElementById('interest-amount').textContent = '$' + interestAmount.toFixed(2);
    document.getElementById('penalty-amount').textContent = '$' + penaltyAmount.toFixed(2);
    document.getElementById('remaining-balance').textContent = '$' + remainingBalance.toFixed(2);
    
    document.getElementById('payment-breakdown').style.display = 'block';
}

// Form submission with loading state
document.getElementById('repaymentForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const spinner = document.getElementById('submitSpinner');
    
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
});

// Auto-calculate on page load
document.addEventListener('DOMContentLoaded', function() {
    calculatePaymentBreakdown();
});
</script>
@endsection
