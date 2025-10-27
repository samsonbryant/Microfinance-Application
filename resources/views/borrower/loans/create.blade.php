@extends('layouts.app')

@section('title', 'Apply for Loan')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4><i class="fas fa-plus me-2 text-primary"></i>Apply for New Loan</h4>
                    <p class="text-muted mb-0">Complete the form below - Real-time updates at each step!</p>
                </div>
                <a href="{{ route('borrower.loans.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Loans
                </a>
            </div>
        </div>
    </div>

    <!-- Livewire Real-Time Application Form -->
    @livewire('borrower-loan-application')
</div>
@endsection

@push('scripts')
<script>
// Listen for application submitted event
window.addEventListener('application-submitted', (event) => {
    if (typeof Swal !== 'undefined') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
        });
        
        Toast.fire({
            icon: 'success',
            title: 'Application Submitted!',
            text: 'Your loan application has been submitted. You will receive real-time updates.'
        });
    }
});
</script>
@endpush
