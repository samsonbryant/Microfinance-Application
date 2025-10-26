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
                            @if(isset($staff) && $staff->count() > 0)
                                @foreach($staff as $member)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <strong>{{ $member->name }}</strong><br>
                                                <small class="text-muted">{{ $member->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-primary">{{ ucfirst($member->role ?? 'N/A') }}</span></td>
                                    <td>{{ $member->branch->name ?? 'Head Office' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $member->is_active ? 'success' : 'danger' }}">
                                            {{ $member->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $member->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('staff.show', $member) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('staff.edit', $member) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            No staff members found. Click "Add Staff" to create one.
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
        var $table = $('#staffTable');
        var hasData = $table.find('tbody tr').length > 0 && !$table.find('tbody tr td[colspan]').length;
        
        if (hasData) {
            $table.DataTable({
                "pageLength": 25,
                "order": [[ 4, "desc" ]]
            });
        }
    });
</script>
@endsection
