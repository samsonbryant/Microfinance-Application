@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Settings</h1>
    <p class="page-subtitle">Configure application settings and system information.</p>
</div>

<div class="row">
    <!-- Application Settings -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Application Settings</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="app_name" class="form-label">Application Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('app_name') is-invalid @enderror" 
                                       id="app_name" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" required>
                                @error('app_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="app_url" class="form-label">Application URL <span class="text-danger">*</span></label>
                                <input type="url" class="form-control @error('app_url') is-invalid @enderror" 
                                       id="app_url" name="app_url" value="{{ old('app_url', $settings['app_url']) }}" required>
                                @error('app_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mail_host" class="form-label">Mail Host</label>
                                <input type="text" class="form-control @error('mail_host') is-invalid @enderror" 
                                       id="mail_host" name="mail_host" value="{{ old('mail_host') }}">
                                @error('mail_host')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mail_port" class="form-label">Mail Port</label>
                                <input type="number" class="form-control @error('mail_port') is-invalid @enderror" 
                                       id="mail_port" name="mail_port" value="{{ old('mail_port') }}" min="1" max="65535">
                                @error('mail_port')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mail_username" class="form-label">Mail Username</label>
                                <input type="text" class="form-control @error('mail_username') is-invalid @enderror" 
                                       id="mail_username" name="mail_username" value="{{ old('mail_username') }}">
                                @error('mail_username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mail_encryption" class="form-label">Mail Encryption</label>
                                <select class="form-select @error('mail_encryption') is-invalid @enderror" 
                                        id="mail_encryption" name="mail_encryption">
                                    <option value="">None</option>
                                    <option value="tls" {{ old('mail_encryption') == 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ old('mail_encryption') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                </select>
                                @error('mail_encryption')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- System Information -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Application Environment</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ $settings['app_env'] === 'production' ? 'success' : 'warning' }}">
                                    {{ ucfirst($settings['app_env']) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Database Connection</label>
                            <p class="form-control-plaintext">{{ ucfirst($settings['db_connection']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mail Driver</label>
                            <p class="form-control-plaintext">{{ ucfirst($settings['mail_driver']) }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cache Driver</label>
                            <p class="form-control-plaintext">{{ ucfirst($settings['cache_driver']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('settings.system-info') }}" class="btn btn-outline-info">
                        <i class="fas fa-info-circle"></i> View Detailed System Info
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Statistics -->
    <div class="col-lg-4">
        <!-- Statistics -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">System Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h4 class="text-primary">{{ $stats['total_users'] }}</h4>
                        <p class="text-muted mb-0">Users</p>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-success">{{ $stats['total_branches'] }}</h4>
                        <p class="text-muted mb-0">Branches</p>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-info">{{ $stats['total_clients'] }}</h4>
                        <p class="text-muted mb-0">Clients</p>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-warning">{{ $stats['total_loans'] }}</h4>
                        <p class="text-muted mb-0">Loans</p>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-secondary">{{ $stats['total_savings_accounts'] }}</h4>
                        <p class="text-muted mb-0">Savings</p>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-dark">{{ $stats['total_transactions'] }}</h4>
                        <p class="text-muted mb-0">Transactions</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <form action="{{ route('settings.clear-cache') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning w-100">
                            <i class="fas fa-broom"></i> Clear Cache
                        </button>
                    </form>
                    <form action="{{ route('settings.backup') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-info w-100">
                            <i class="fas fa-download"></i> Create Backup
                        </button>
                    </form>
                    <a href="{{ route('settings.system-info') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-info-circle"></i> System Info
                    </a>
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">System Health</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Database</span>
                        <span class="badge bg-success">Healthy</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Cache</span>
                        <span class="badge bg-success">Healthy</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Storage</span>
                        <span class="badge bg-success">Healthy</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Mail</span>
                        <span class="badge bg-warning">Warning</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
