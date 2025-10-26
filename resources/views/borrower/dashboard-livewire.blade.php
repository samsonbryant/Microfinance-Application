@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="container-fluid" style="font-family: 'Inter', sans-serif;">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-gray-800">
                <i class="fas fa-tachometer-alt text-primary"></i> My Dashboard
            </h1>
            <p class="text-muted mb-0">Welcome back, {{ auth()->user()->name }}! Here's your financial overview.</p>
        </div>
        <div class="text-end">
            <small class="text-muted d-block">
                <i class="fas fa-clock"></i> {{ now()->format('l, F j, Y - g:i A') }}
            </small>
        </div>
    </div>

    <!-- Real-time Livewire Dashboard Component -->
    @livewire('borrower-dashboard')
    
    <!-- Loan Application Status (Real-time) -->
    <div class="mt-4">
        <h4 class="mb-3 fw-semibold">
            <i class="fas fa-clipboard-list text-primary"></i> Loan Application Status
        </h4>
        @livewire('loan-application-status')
    </div>
    
</div>
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    body {
        background-color: #F8FAFC;
    }
    
    .text-gray-800 {
        color: #1E293B;
    }
</style>
@endpush

