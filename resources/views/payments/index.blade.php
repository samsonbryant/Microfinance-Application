@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="page-header">
    <h1 class="page-title">Payment History</h1>
    <p class="page-subtitle">View and manage payment transactions.</p>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Payment Transactions</h6>
                <a href="{{ route('payments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Payment
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="paymentsTable">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Client</th>
                                <th>Loan</th>
                                <th>Amount</th>
                                <th>Payment Type</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        Payment transactions will be displayed here once the payment system is fully integrated.
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#paymentsTable').DataTable({
            "pageLength": 25,
            "order": [[ 5, "desc" ]]
        });
    });
</script>
@endsection
