@extends('layouts.app')

@section('title', 'Edit Staff Member')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-edit text-warning me-2"></i>Edit Staff Member
            </h1>
            <p class="text-muted mb-0">{{ $staff->name }}</p>
        </div>
        <a href="{{ route('staff.show', $staff) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <form action="{{ route('staff.update', $staff) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow">
            <div class="card-header bg-warning">
                <h6 class="m-0 font-weight-bold">Staff Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $staff->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email', $staff->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="">Select Role</option>
                            @foreach(['admin', 'general_manager', 'branch_manager', 'loan_officer', 'accountant', 'hr'] as $role)
                                <option value="{{ $role }}" {{ (old('role', $staff->roles->first()->name ?? '') === $role) ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $role)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="branch_id" class="form-label">Branch</label>
                        <select class="form-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                            <option value="">Head Office</option>
                            @foreach(\App\Models\Branch::all() as $branch)
                                <option value="{{ $branch->id }}" {{ (old('branch_id', $staff->branch_id) == $branch->id) ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $staff->phone) }}">
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('staff.show', $staff) }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i>Update Staff Member
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

