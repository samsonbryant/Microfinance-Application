@extends('layouts.app')

@section('title', 'Staff Management')

@section('content')
<div class="page-header">
    <h1 class="page-title">Staff Management</h1>
    <p class="page-subtitle">Manage staff members and their information.</p>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Staff Members</h6>
                <a href="{{ route('staff.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Staff
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="staffTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Branch</th>
                                <th>Employment Status</th>
                                <th>Hire Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        Staff members will be displayed here once the staff management system is fully integrated.
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
        $('#staffTable').DataTable({
            "pageLength": 25,
            "order": [[ 4, "desc" ]]
        });
    });
</script>
@endsection
