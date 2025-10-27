@extends('layouts.app')

@section('title', 'Branch Collections & Payments')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-money-bill-wave text-success me-2"></i>Collections & Payments
            </h1>
            <p class="text-muted mb-0">{{ auth()->user()->branch->name ?? 'Branch' }} - Real-time Payment Processing</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('branch-manager.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <a href="{{ route('loan-repayments.index') }}" class="btn btn-primary">
                <i class="fas fa-history"></i> Payment History
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Livewire Collections Component -->
    @livewire('branch-manager-collections')
</div>
@endsection

@push('scripts')
<script>
// Listen for payment processed event
window.addEventListener('payment-processed', () => {
    // Show success notification
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
    
    Toast.fire({
        icon: 'success',
        title: 'Payment processed successfully!'
    });
});
</script>
@endpush

