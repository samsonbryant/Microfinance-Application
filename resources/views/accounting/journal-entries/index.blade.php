@extends('layouts.app')

@section('title', 'Journal Entries')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-invoice text-primary me-2"></i>Journal Entries
            </h1>
            <p class="text-muted mb-0">Manual accounting entries and adjustments</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('accounting.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <a href="{{ route('accounting.journal-entries.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Entry
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Approval</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ $journalEntries->where('status', 'pending')->count() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Posted</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ $journalEntries->where('status', 'posted')->count() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Journal Entries Table -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h6 class="m-0 font-weight-bold">All Journal Entries</h6>
        </div>
        <div class="card-body">
            @if($journalEntries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Entry #</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th class="text-end">Amount</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($journalEntries as $entry)
                            <tr>
                                <td><strong>{{ $entry->entry_number }}</strong></td>
                                <td>{{ $entry->transaction_date->format('M d, Y') }}</td>
                                <td>{{ $entry->description }}</td>
                                <td>
                                    @if($entry->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($entry->status === 'approved')
                                        <span class="badge bg-info">Approved</span>
                                    @elseif($entry->status === 'posted')
                                        <span class="badge bg-success">Posted</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($entry->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong>${{ number_format($entry->lines->sum('debit_amount'), 2) }}</strong>
                                </td>
                                <td>{{ $entry->user->name ?? 'N/A' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('accounting.journal-entries.show', $entry) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($entry->status === 'pending')
                                            <button class="btn btn-sm btn-success" onclick="approveEntry({{ $entry->id }})">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $journalEntries->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <h5>No Journal Entries</h5>
                    <p class="text-muted">No journal entries found. Create your first entry!</p>
                    <a href="{{ route('accounting.journal-entries.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Journal Entry
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

