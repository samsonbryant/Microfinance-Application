@extends('layouts.app')

@section('title', 'Users')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Users</h1>
    <p class="page-subtitle">Manage system users and their permissions.</p>
</div>

<!-- Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2">
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New User
                </a>
            </div>
            <div class="d-flex gap-2">
                <select class="form-select" style="width: 150px;" onchange="filterByRole(this.value)">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="manager" {{ request('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="officer" {{ request('role') == 'officer' ? 'selected' : '' }}>Officer</option>
                    <option value="clerk" {{ request('role') == 'clerk' ? 'selected' : '' }}>Clerk</option>
                </select>
                <select class="form-select" style="width: 150px;" onchange="filterByStatus(this.value)">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <input type="text" class="form-control" placeholder="Search users..." style="width: 300px;">
                <button class="btn btn-outline-secondary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Users</h6>
    </div>
    <div class="card-body">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Branch</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-2">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        @if($user->id === auth()->id())
                                            <span class="badge bg-info ms-1">You</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ 
                                    $user->role === 'admin' ? 'danger' : 
                                    ($user->role === 'manager' ? 'warning' : 
                                    ($user->role === 'officer' ? 'info' : 'secondary')) 
                                }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>{{ $user->branch->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}</td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-{{ $user->is_active ? 'warning' : 'success' }}" 
                                                    title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas fa-{{ $user->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $users->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Users Found</h5>
                <p class="text-muted">Start by adding your first user.</p>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add First User
                </a>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
</style>
@endpush

<script>
function filterByRole(role) {
    const url = new URL(window.location);
    if (role) {
        url.searchParams.set('role', role);
    } else {
        url.searchParams.delete('role');
    }
    window.location = url;
}

function filterByStatus(status) {
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    window.location = url;
}
</script>
@endsection
