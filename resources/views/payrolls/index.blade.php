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
                            @if(isset($payrolls) && $payrolls->count() > 0)
                                @foreach($payrolls as $payroll)
                                <tr>
                                    <td>
                                        {{ $payroll->staff->user->name ?? 'N/A' }}<br>
                                        <small class="text-muted">{{ $payroll->staff->employee_id ?? '' }}</small>
                                    </td>
                                    <td>{{ $payroll->month }}</td>
                                    <td class="text-primary"><strong>${{ number_format($payroll->basic_salary ?? 0, 2) }}</strong></td>
                                    <td class="text-danger">${{ number_format($payroll->deductions ?? 0, 2) }}</td>
                                    <td class="text-success"><strong>${{ number_format($payroll->net_salary ?? 0, 2) }}</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $payroll->status === 'paid' ? 'success' : ($payroll->status === 'pending' ? 'warning' : 'info') }}">
                                            {{ ucfirst($payroll->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('payrolls.show', $payroll) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($payroll->status === 'pending')
                                                <form action="{{ route('payrolls.process', $payroll) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Process Payment">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            No payroll records found. Click "Process Payroll" to generate payroll.
                                        </div>
                                    </td>
                                </tr>
                            @endif
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
        // Only initialize DataTable if there's actual data (not just placeholder)
        var $table = $('#payrollsTable');
        var hasData = $table.find('tbody tr').length > 0 && !$table.find('tbody tr td[colspan]').length;
        
        if (hasData) {
            $table.DataTable({
                "pageLength": 25,
                "order": [[ 1, "desc" ]]
            });
        }
    });
</script>
@endsection
