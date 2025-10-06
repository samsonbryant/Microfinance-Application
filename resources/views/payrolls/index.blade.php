@extends('layouts.app')

@section('title', 'Payroll Management')

@section('content')
<div class="page-header">
    <h1 class="page-title">Payroll Management</h1>
    <p class="page-subtitle">Manage staff payroll and salary processing.</p>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Payroll Records</h6>
                <a href="{{ route('payrolls.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Process Payroll
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="payrollsTable">
                        <thead>
                            <tr>
                                <th>Staff Member</th>
                                <th>Month/Year</th>
                                <th>Gross Salary</th>
                                <th>Deductions</th>
                                <th>Net Salary</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        Payroll records will be displayed here once the payroll system is fully integrated.
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
        $('#payrollsTable').DataTable({
            "pageLength": 25,
            "order": [[ 1, "desc" ]]
        });
    });
</script>
@endsection
