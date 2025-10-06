@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle">View and manage your account information.</p>
</div>

<div class="row">
    <!-- Profile Information -->
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Profile Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Name</label>
                            <p class="form-control-plaintext">{{ $user->name }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Username</label>
                            <p class="form-control-plaintext">{{ $user->username }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <p class="form-control-plaintext">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone Number</label>
                            <p class="form-control-plaintext">{{ $user->phone ?? 'Not provided' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Role</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Branch</label>
                            <p class="form-control-plaintext">{{ $user->branch->name ?? 'No branch assigned' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Account Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Member Since</label>
                            <p class="form-control-plaintext">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Actions -->
    <div class="col-lg-4">
        <!-- Avatar Section -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Profile Picture</h6>
            </div>
            <div class="card-body text-center">
                <div class="profile-avatar mb-3">
                    <div class="avatar-circle">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                </div>
                <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" class="d-inline">
                    @csrf
                    @method('PUT')
                    <input type="file" name="avatar" id="avatar" class="d-none" accept="image/*" onchange="this.form.submit()">
                    <label for="avatar" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-camera"></i> Change Avatar
                    </label>
                </form>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-info">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                        @error('current_password')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        @error('password')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.profile-avatar .avatar-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: bold;
    color: white;
    margin: 0 auto;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.form-control-plaintext {
    padding: 0.375rem 0;
    margin-bottom: 0;
    line-height: 1.5;
    color: var(--bs-body-color);
    background-color: transparent;
    border: solid transparent;
    border-width: 1px 0;
}
</style>
@endpush
@endsection
