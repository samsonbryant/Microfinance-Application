@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container-fluid" style="font-family: 'Inter', sans-serif;">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-user-circle text-primary"></i> My Profile
            </h2>
            <p class="text-muted mb-0">Manage your personal information</p>
        </div>
        <a href="{{ route('borrower.dashboard') }}" class="btn btn-secondary" style="border-radius: 8px;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" style="border-radius: 12px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" style="border-radius: 12px;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Profile Form -->
        <div class="col-lg-8">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-id-card text-primary"></i> Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('borrower.profile.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <!-- Full Name -->
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-semibold">
                                    <i class="fas fa-user text-primary"></i> Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $user->name) }}" 
                                       required
                                       style="border-radius: 8px;">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="fas fa-envelope text-success"></i> Email Address <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}" 
                                       required
                                       style="border-radius: 8px;">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">
                                    <i class="fas fa-phone text-info"></i> Phone Number
                                </label>
                                <input type="text" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone', $user->phone) }}"
                                       style="border-radius: 8px;">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Date of Birth -->
                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label fw-semibold">
                                    <i class="fas fa-birthday-cake text-warning"></i> Date of Birth
                                </label>
                                <input type="date" 
                                       class="form-control @error('date_of_birth') is-invalid @enderror" 
                                       id="date_of_birth" 
                                       name="date_of_birth" 
                                       value="{{ old('date_of_birth', $client->date_of_birth ?? '') }}"
                                       style="border-radius: 8px;">
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Address -->
                            <div class="col-md-12">
                                <label for="address" class="form-label fw-semibold">
                                    <i class="fas fa-map-marker-alt text-danger"></i> Address
                                </label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" 
                                          name="address" 
                                          rows="3"
                                          style="border-radius: 8px;">{{ old('address', $client->address ?? '') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- National ID -->
                            <div class="col-md-6">
                                <label for="national_id" class="form-label fw-semibold">
                                    <i class="fas fa-id-card text-secondary"></i> National ID Number
                                </label>
                                <input type="text" 
                                       class="form-control @error('national_id') is-invalid @enderror" 
                                       id="national_id" 
                                       name="national_id" 
                                       value="{{ old('national_id', $client->national_id ?? '') }}"
                                       style="border-radius: 8px;">
                                @error('national_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="col-md-12">
                                <hr class="my-3">
                                <button type="submit" class="btn btn-primary" style="border-radius: 8px;">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                                <a href="{{ route('borrower.dashboard') }}" class="btn btn-secondary" style="border-radius: 8px;">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Profile Summary -->
        <div class="col-lg-4">
            <!-- Account Status Card -->
            <div class="card mb-3" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-header bg-white border-0 pt-4">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-info-circle text-info"></i> Account Status
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <strong>Account Type:</strong>
                            <span class="badge bg-primary float-end">Borrower</span>
                        </li>
                        <li class="mb-2">
                            <strong>Status:</strong>
                            <span class="badge bg-{{ $client->status === 'active' ? 'success' : 'secondary' }} float-end">
                                {{ ucfirst($client->status ?? 'Active') }}
                            </span>
                        </li>
                        <li class="mb-2">
                            <strong>KYC Status:</strong>
                            <span class="badge bg-{{ $client->kyc_status === 'verified' ? 'success' : 'warning' }} float-end">
                                {{ ucfirst($client->kyc_status ?? 'Pending') }}
                            </span>
                        </li>
                        <li class="mb-2">
                            <strong>Member Since:</strong>
                            <span class="text-muted float-end">{{ $user->created_at->format('M d, Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-header bg-white border-0 pt-4">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-chart-bar text-success"></i> Quick Stats
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $activeLoans = $client->loans()->whereIn('status', ['active', 'disbursed'])->count();
                        $savingsCount = $client->savingsAccounts()->count();
                        $creditScore = $client->credit_score ?? 0;
                    @endphp
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Active Loans</span>
                        <strong class="text-primary">{{ $activeLoans }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Savings Accounts</span>
                        <strong class="text-success">{{ $savingsCount }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Credit Score</span>
                        <strong class="text-{{ $creditScore > 700 ? 'success' : ($creditScore > 500 ? 'warning' : 'danger') }}">
                            {{ $creditScore }}
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card mt-3" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-header bg-primary text-white border-0 pt-4">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-question-circle"></i> Need Help?
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">Keep your profile up to date to:</p>
                    <ul class="small mb-3">
                        <li>Apply for loans</li>
                        <li>Receive important notifications</li>
                        <li>Improve your credit score</li>
                        <li>Access all services</li>
                    </ul>
                    <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary w-100" style="border-radius: 8px;">
                        <i class="fas fa-bell"></i> View Notifications
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
