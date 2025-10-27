@extends('layouts.app')

@section('title', 'General Ledger')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-book text-primary me-2"></i>General Ledger
            </h1>
            <p class="text-muted mb-0">Complete transaction history for all accounts</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('accounting.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('accounting.general-ledger') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Account</label>
                    <select name="account_id" class="form-select">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->code }} - {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date', now()->startOfMonth()->toDateString()) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date', now()->toDateString()) }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ledger Entries -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-list me-2"></i>Ledger Entries
            </h6>
        </div>
        <div class="card-body">
            @if($entries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Account</th>
                                <th>Description</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                <th class="text-end">Balance</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entries as $entry)
                            <tr>
                                <td>{{ $entry->transaction_date->format('M d, Y') }}</td>
                                <td>
                                    <strong>{{ $entry->account->code }}</strong><br>
                                    <small class="text-muted">{{ $entry->account->name }}</small>
                                </td>
                                <td>{{ $entry->description }}</td>
                                <td class="text-end">
                                    @if($entry->debit_amount > 0)
                                        <strong class="text-danger">${{ number_format($entry->debit_amount, 2) }}</strong>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($entry->credit_amount > 0)
                                        <strong class="text-success">${{ number_format($entry->credit_amount, 2) }}</strong>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong>${{ number_format($entry->running_balance ?? 0, 2) }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $entry->reference_number ?? '-' }}</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $entries->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                    <h5>No Ledger Entries</h5>
                    <p class="text-muted">No transactions found for the selected criteria.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

