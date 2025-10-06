@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="page-header">
    <h1 class="page-title">Audit Logs</h1>
    <p class="page-subtitle">System activity and audit trail.</p>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">System Audit Logs</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="auditLogsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Event</th>
                                <th>Subject</th>
                                <th>User</th>
                                <th>Date</th>
                                <th>Properties</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        Audit logs will be displayed here once the activity log system is fully integrated.
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
        $('#auditLogsTable').DataTable({
            "pageLength": 25,
            "order": [[ 4, "desc" ]]
        });
    });
</script>
@endsection
