@extends('layouts.app')

@section('title', 'Branches')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Branches</h1>
    <p class="page-subtitle">Manage microfinance branch locations and operations.</p>
</div>

<!-- Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2">
                <a href="{{ route('branches.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Branch
                </a>
            </div>
            <div class="d-flex gap-2">
                <input type="text" class="form-control" placeholder="Search branches..." style="width: 300px;">
                <button class="btn btn-outline-secondary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Branches Grid -->
<div class="row">
    @forelse($branches as $branch)
    <div class="col-lg-6 col-xl-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">{{ $branch->name }}</h6>
                <span class="badge bg-{{ $branch->is_active ? 'success' : 'danger' }}">
                    {{ $branch->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Code:</strong> {{ $branch->code }}<br>
                    <strong>Manager:</strong> {{ $branch->manager_name }}<br>
                    <strong>Phone:</strong> {{ $branch->phone }}<br>
                    <strong>Email:</strong> {{ $branch->email }}
                </div>
                
                <div class="mb-3">
                    <strong>Address:</strong><br>
                    {{ $branch->address }}<br>
                    {{ $branch->city }}, {{ $branch->state }}<br>
                    {{ $branch->country }}
                </div>

                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="border-end">
                            <h5 class="mb-0 text-primary">{{ $branch->users_count }}</h5>
                            <small class="text-muted">Users</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-end">
                            <h5 class="mb-0 text-success">{{ $branch->clients_count }}</h5>
                            <small class="text-muted">Clients</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <h5 class="mb-0 text-warning">{{ $branch->loans_count }}</h5>
                        <small class="text-muted">Loans</small>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('branches.show', $branch) }}" class="btn btn-sm btn-outline-primary flex-fill">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="{{ route('branches.edit', $branch) }}" class="btn btn-sm btn-outline-warning flex-fill">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('branches.destroy', $branch) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this branch?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger flex-fill">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Branches Found</h5>
                <p class="text-muted">Start by creating your first branch location.</p>
                <a href="{{ route('branches.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First Branch
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($branches->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $branches->links() }}
</div>
@endif
@endsection
