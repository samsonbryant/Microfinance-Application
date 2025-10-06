@extends('layouts.app')

@section('title', 'Edit Branch')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Edit Branch</h1>
    <p class="page-subtitle">Branch: {{ $branch->name }}</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Branch Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('branches.update', $branch) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Branch Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $branch->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code" class="form-label">Branch Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" value="{{ old('code', $branch->code) }}" 
                                       style="text-transform: uppercase" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="3" required>{{ old('address', $branch->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                       id="city" name="city" value="{{ old('city', $branch->city) }}" required>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                       id="state" name="state" value="{{ old('state', $branch->state) }}" required>
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                       id="country" name="country" value="{{ old('country', $branch->country) }}" required>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $branch->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $branch->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="manager_name" class="form-label">Manager Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('manager_name') is-invalid @enderror" 
                                       id="manager_name" name="manager_name" value="{{ old('manager_name', $branch->manager_name) }}" required>
                                @error('manager_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="is_active" class="form-label">Status</label>
                                <select class="form-select @error('is_active') is-invalid @enderror" 
                                        id="is_active" name="is_active">
                                    <option value="1" {{ old('is_active', $branch->is_active) == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active', $branch->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Branch
                        </button>
                        <a href="{{ route('branches.show', $branch) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Branch Summary -->
    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Branch Summary</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="status-circle {{ $branch->is_active ? 'active' : 'inactive' }}">
                        <i class="fas fa-building"></i>
                    </div>
                    <h5>{{ $branch->is_active ? 'Active' : 'Inactive' }}</h5>
                </div>
                
                <div class="mb-3">
                    <strong>Code:</strong> {{ $branch->code }}
                </div>
                <div class="mb-3">
                    <strong>Manager:</strong> {{ $branch->manager_name }}
                </div>
                <div class="mb-3">
                    <strong>Created:</strong> {{ $branch->created_at->format('M d, Y') }}
                </div>
                <div class="mb-3">
                    <strong>Last Updated:</strong> {{ $branch->updated_at->format('M d, Y') }}
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow mt-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('branches.show', $branch) }}" class="btn btn-outline-info">
                        <i class="fas fa-eye"></i> View Branch
                    </a>
                    <a href="{{ route('clients.create', ['branch_id' => $branch->id]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus"></i> Add Client
                    </a>
                    <a href="{{ route('loans.create', ['branch_id' => $branch->id]) }}" class="btn btn-outline-success">
                        <i class="fas fa-hand-holding-usd"></i> New Loan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.status-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto;
}

.status-circle.active {
    background-color: #d4edda;
    color: #155724;
}

.status-circle.inactive {
    background-color: #f8d7da;
    color: #721c24;
}
</style>
@endpush

@push('scripts')
<script>
document.getElementById('code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>
@endpush
@endsection
