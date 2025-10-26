@extends('layouts.app')

@section('title', 'Payment Schedule - Loan #' . $loan->loan_number)

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calendar-alt text-primary me-2"></i>Payment Schedule
            </h1>
            <p class="text-muted mb-0">Loan #{{ $loan->loan_number }} - {{ $loan->client->first_name }} {{ $loan->client->last_name }}</p>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print Schedule
            </button>
            <a href="{{ route('loans.show', $loan->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Loan
            </a>
        </div>
    </div>

    <!-- Loan Summary Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Loan Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <label>Principal Amount:</label>
                                <div class="value">${{ number_format($loan->amount, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <label>Interest Rate:</label>
                                <div class="value">{{ $loan->interest_rate }}% annually</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <label>Loan Term:</label>
                                <div class="value">{{ $loan->term_months }} months</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <label>Monthly Payment:</label>
                                <div class="value text-primary">${{ number_format($loan->monthly_payment ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <label>Total Interest:</label>
                                <div class="value">${{ number_format($loan->total_interest ?? 0, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <label>Total Amount:</label>
                                <div class="value text-success">${{ number_format($loan->total_amount ?? $loan->amount, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <label>Outstanding Balance:</label>
                                <div class="value text-warning">${{ number_format($loan->outstanding_balance, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <label>Status:</label>
                                <div class="value">
                                    <span class="badge bg-{{ $loan->status === 'active' ? 'success' : ($loan->status === 'pending' ? 'warning' : 'info') }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Schedule Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table me-2"></i>Payment Schedule
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Principal</th>
                                    <th>Interest</th>
                                    <th>Total Payment</th>
                                    <th>Balance</th>
                                    <th class="no-print">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($schedule && count($schedule) > 0)
                                    @foreach($schedule as $payment)
                                    <tr class="{{ $payment['status'] === 'paid' ? 'table-success' : ($payment['status'] === 'overdue' ? 'table-danger' : '') }}">
                                        <td>{{ $payment['payment_number'] }}</td>
                                        <td>{{ date('M d, Y', strtotime($payment['due_date'])) }}</td>
                                        <td>${{ number_format($payment['principal'], 2) }}</td>
                                        <td>${{ number_format($payment['interest'], 2) }}</td>
                                        <td class="fw-bold">${{ number_format($payment['payment_amount'], 2) }}</td>
                                        <td>${{ number_format($payment['balance'], 2) }}</td>
                                        <td class="no-print">
                                            @if($payment['status'] === 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @elseif($payment['status'] === 'overdue')
                                                <span class="badge bg-danger">Overdue</span>
                                            @elseif($payment['status'] === 'due')
                                                <span class="badge bg-warning">Due</span>
                                            @else
                                                <span class="badge bg-secondary">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Payment schedule not yet generated</p>
                                            @if(auth()->user()->hasRole(['admin', 'loan_officer']))
                                                <button class="btn btn-primary btn-sm mt-2" onclick="generateSchedule({{ $loan->id }})">
                                                    <i class="fas fa-cog me-1"></i>Generate Schedule
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                            @if($schedule && count($schedule) > 0)
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td colspan="2">TOTALS:</td>
                                    <td>${{ number_format(collect($schedule)->sum('principal'), 2) }}</td>
                                    <td>${{ number_format(collect($schedule)->sum('interest'), 2) }}</td>
                                    <td>${{ number_format(collect($schedule)->sum('payment_amount'), 2) }}</td>
                                    <td>$0.00</td>
                                    <td class="no-print"></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Information (for print) -->
    <div class="row mt-4 print-only">
        <div class="col-12">
            <div class="text-center">
                <hr>
                <p class="text-muted mb-1">
                    <strong>{{ config('app.name', 'Microfinance System') }}</strong>
                </p>
                <p class="text-muted mb-1">
                    Generated on {{ now()->format('F d, Y \a\t g:i A') }}
                </p>
                <p class="text-muted small">
                    This payment schedule is for informational purposes. Please contact your loan officer for any questions.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.info-box {
    margin-bottom: 1rem;
}

.info-box label {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
    display: block;
}

.info-box .value {
    font-size: 1.1rem;
    font-weight: 500;
    color: #495057;
}

/* Print Styles */
@media print {
    .no-print {
        display: none !important;
    }
    
    .print-only {
        display: block !important;
    }
    
    body {
        font-size: 12px;
    }
    
    .card {
        border: 1px solid #dee2e6;
        box-shadow: none;
    }
    
    .table {
        font-size: 11px;
    }
    
    .table th,
    .table td {
        padding: 0.5rem 0.25rem;
    }
    
    .btn {
        display: none;
    }
    
    .container-fluid {
        padding: 0;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        color: #000 !important;
        -webkit-print-color-adjust: exact;
    }
    
    .badge {
        border: 1px solid #000;
        color: #000 !important;
        background-color: transparent !important;
    }
    
    .table-striped > tbody > tr:nth-of-type(odd) > td {
        background-color: #f9f9f9 !important;
        -webkit-print-color-adjust: exact;
    }
}

.print-only {
    display: none;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .info-box .value {
        font-size: 1rem;
    }
}
</style>

<script>
function generateSchedule(loanId) {
    if (confirm('Generate payment schedule for this loan?')) {
        fetch(`/loans/${loanId}/generate-schedule`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error generating schedule: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Network error occurred');
        });
    }
}

// Auto-print if print parameter is present
if (new URLSearchParams(window.location.search).get('print') === 'true') {
    window.onload = function() {
        window.print();
    };
}
</script>
@endsection
