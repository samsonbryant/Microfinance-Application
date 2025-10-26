@extends('layouts.app')

@section('title', 'Staff Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user text-primary me-2"></i>Staff Member Details
            </h1>
            <p class="text-muted mb-0">{{ $staff->name }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('staff.edit', $staff) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            <a href="{{ route('staff.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <!-- Personal Information -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Personal Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Full Name:</th>
                            <td><strong>{{ $staff->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $staff->email }}</td>
                        </tr>
                        <tr>
                            <th>Username:</th>
                            <td>{{ $staff->username ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Role:</th>
                            <td>
                                @foreach($staff->roles as $role)
                                    <span class="badge bg-primary">{{ ucfirst($role->name) }}</span>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th>Branch:</th>
                            <td>{{ $staff->branch->name ?? 'Head Office' }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $staff->is_active ? 'success' : 'danger' }}">
                                    {{ $staff->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Joined:</th>
                            <td>{{ $staff->created_at->format('M d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Activity Summary -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Activity Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Loans Created:</strong><br>
                                {{ \App\Models\Loan::where('created_by', $staff->id)->count() }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Clients Created:</strong><br>
                                {{ \App\Models\Client::where('created_by', $staff->id)->count() }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Loans Approved:</strong><br>
                                {{ \App\Models\Loan::where('approved_by', $staff->id)->count() }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Last Login:</strong><br>
                                {{ $staff->last_login_at ? $staff->last_login_at->diffForHumans() : 'Never' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="m-0 font-weight-bold">Recent Activity</h6>
                </div>
                <div class="card-body">
                    @php
                        $activities = \Spatie\Activitylog\Models\Activity::where('causer_id', $staff->id)
                            ->where('causer_type', 'App\Models\User')
                            ->latest()
                            ->take(10)
                            ->get();
                    @endphp

                    @if($activities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Activity</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activities as $activity)
                                    <tr>
                                        <td><small>{{ $activity->created_at->format('M d, H:i') }}</small></td>
                                        <td><span class="badge bg-info">{{ $activity->log_name }}</span></td>
                                        <td>{{ $activity->description }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No recent activity</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Total Loans</span>
                            <strong class="text-primary">{{ \App\Models\Loan::where('created_by', $staff->id)->count() }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Total Clients</span>
                            <strong class="text-success">{{ \App\Models\Client::where('created_by', $staff->id)->count() }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Approvals</span>
                            <strong class="text-info">{{ \App\Models\Loan::where('approved_by', $staff->id)->count() }}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Member Since</span>
                            <strong>{{ $staff->created_at->diffForHumans() }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow mb-4">
                <div class="card-header bg-warning">
                    <h6 class="m-0 font-weight-bold">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('staff.edit', $staff) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Edit Details
                        </a>
                        @if($staff->is_active)
                            <form action="{{ route('staff.deactivate', $staff) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Deactivate this staff member?')">
                                    <i class="fas fa-ban me-1"></i>Deactivate
                                </button>
                            </form>
                        @else
                            <form action="{{ route('staff.activate', $staff) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check me-1"></i>Activate
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('payrolls.index', ['user_id' => $staff->id]) }}" class="btn btn-info">
                            <i class="fas fa-dollar-sign me-1"></i>View Payroll
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

