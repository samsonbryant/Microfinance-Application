@extends('layouts.app')

@section('title', 'Trial Balance')

@section('content')
<div class="page-header">
    <h1 class="page-title">Trial Balance</h1>
    <p class="page-subtitle">Summary of all account balances</p>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-balance-scale me-2"></i>Trial Balance as of {{ now()->format('F d, Y') }}
                    </h6>
                    <div>
                        <a href="{{ route('general-ledger.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Ledger
                        </a>
                        <button class="btn btn-sm btn-primary" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Print
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Account Code</th>
                                <th>Account Name</th>
                                <th class="text-end">Debits</th>
                                <th class="text-end">Credits</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalDebits = 0;
                                $totalCredits = 0;
                            @endphp
                            @forelse($trialBalance as $account)
                                @php
                                    $totalDebits += $account['debits'];
                                    $totalCredits += $account['credits'];
                                @endphp
                                <tr>
                                    <td>{{ $account['account_code'] }}</td>
                                    <td>{{ $account['account_name'] }}</td>
                                    <td class="text-end">${{ number_format($account['debits'], 2) }}</td>
                                    <td class="text-end">${{ number_format($account['credits'], 2) }}</td>
                                    <td class="text-end">
                                        <strong>${{ number_format(abs($account['balance']), 2) }}</strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No ledger entries found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="2" class="text-end">TOTALS:</th>
                                <th class="text-end">${{ number_format($totalDebits, 2) }}</th>
                                <th class="text-end">${{ number_format($totalCredits, 2) }}</th>
                                <th class="text-end">${{ number_format(abs($totalDebits - $totalCredits), 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if(abs($totalDebits - $totalCredits) > 0.01)
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Trial balance is out of balance by ${{ number_format(abs($totalDebits - $totalCredits), 2) }}
                    </div>
                @else
                    <div class="alert alert-success mt-3">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Balanced:</strong> Debits equal credits
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .btn, .page-header, .card-header .btn-group { display: none; }
    }
</style>
@endsection

