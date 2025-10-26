@extends('layouts.app')

@section('content')
<div class="container-fluid" style="font-family: 'Inter', sans-serif;">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-receipt text-danger"></i> Expenses
            </h2>
            <p class="text-muted mb-0">Manage and track all expense transactions</p>
        </div>
        <a href="{{ route('accounting.expenses.create') }}" class="btn btn-danger" style="border-radius: 8px;">
            <i class="fas fa-plus"></i> New Expense
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select id="statusFilter" class="form-select" style="border-radius: 8px;">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="posted">Posted</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">From Date</label>
                    <input type="date" id="fromDateFilter" class="form-control" style="border-radius: 8px;">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">To Date</label>
                    <input type="date" id="toDateFilter" class="form-control" style="border-radius: 8px;">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button id="filterBtn" class="btn btn-primary w-100" style="border-radius: 8px;">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Expenses Table Card -->
    <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <div class="card-body">
            <div class="table-responsive">
                <table id="expensesTable" class="table table-hover align-middle" style="width: 100%;">
                    <thead class="bg-light">
                        <tr>
                            <th>Expense #</th>
                            <th>Date</th>
                            <th>Account</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#expensesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('accounting.expenses.index') }}",
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.from_date = $('#fromDateFilter').val();
                d.to_date = $('#toDateFilter').val();
            }
        },
        columns: [
            { data: 'expense_number', name: 'expense_number' },
            { data: 'date', name: 'transaction_date' },
            { data: 'account_name', name: 'account_name' },
            { data: 'description', name: 'description', orderable: false },
            { data: 'amount', name: 'amount', className: 'text-end fw-bold' },
            { data: 'payment_method', name: 'payment_method' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });

    $('#filterBtn').click(function() {
        table.draw();
    });
});

function approveExpense(id) {
    if (confirm('Are you sure you want to approve this expense?')) {
        $.post("{{ url('accounting/expenses') }}/" + id + "/approve", {
            _token: "{{ csrf_token() }}"
        }).done(function(response) {
            alert(response.message);
            $('#expensesTable').DataTable().ajax.reload();
        }).fail(function(xhr) {
            alert(xhr.responseJSON?.message || 'Error approving expense');
        });
    }
}

function postExpense(id) {
    if (confirm('Are you sure you want to post this expense? This will create journal entries.')) {
        $.post("{{ url('accounting/expenses') }}/" + id + "/post", {
            _token: "{{ csrf_token() }}"
        }).done(function(response) {
            alert(response.message);
            $('#expensesTable').DataTable().ajax.reload();
        }).fail(function(xhr) {
            alert(xhr.responseJSON?.message || 'Error posting expense');
        });
    }
}
</script>
@endpush
@endsection

