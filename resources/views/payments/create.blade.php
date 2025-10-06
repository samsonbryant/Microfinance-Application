@extends('layouts.app')

@section('title', 'Make Payment')

@section('content')
<div class="page-header">
    <h1 class="page-title">Make Payment</h1>
    <p class="page-subtitle">Process a new payment transaction.</p>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Payment Information</h6>
            </div>
            <div class="card-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="client_id">Client</label>
                                <select class="form-control" id="client_id" name="client_id" required>
                                    <option value="">Select Client</option>
                                    <!-- Clients will be populated here -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loan_id">Loan</label>
                                <select class="form-control" id="loan_id" name="loan_id" required>
                                    <option value="">Select Loan</option>
                                    <!-- Loans will be populated here -->
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Payment Amount</label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_type">Payment Type</label>
                                <select class="form-control" id="payment_type" name="payment_type" required>
                                    <option value="">Select Type</option>
                                    <option value="principal">Principal</option>
                                    <option value="interest">Interest</option>
                                    <option value="penalty">Penalty</option>
                                    <option value="full">Full Payment</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_method">Payment Method</label>
                                <select class="form-control" id="payment_method" name="payment_method" required>
                                    <option value="">Select Method</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="check">Check</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_date">Payment Date</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-credit-card"></i> Process Payment
                        </button>
                        <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
