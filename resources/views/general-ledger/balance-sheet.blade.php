@extends('layouts.app')

@section('title', 'Balance Sheet')

@section('content')
<div class="page-header">
    <h1 class="page-title">Balance Sheet</h1>
    <p class="page-subtitle">Financial position as of {{ now()->format('F d, Y') }}</p>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-invoice me-2"></i>Balance Sheet
                    </h6>
                    <div>
                        <a href="{{ route('general-ledger.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                        <button class="btn btn-sm btn-primary" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Print
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3">Assets</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Account</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($balanceSheet['asset_accounts']) && count($balanceSheet['asset_accounts']) > 0)
                                        @foreach($balanceSheet['asset_accounts'] as $account)
                                            <tr>
                                                <td>{{ $account['name'] }}</td>
                                                <td class="text-end">${{ number_format($account['amount'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No asset accounts</td>
                                        </tr>
                                    @endif
                                    <tr class="table-secondary fw-bold">
                                        <td>Total Assets</td>
                                        <td class="text-end">${{ number_format($balanceSheet['assets'] ?? 0, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="mb-3">Liabilities & Equity</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Account</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="table-light"><td colspan="2"><strong>Liabilities</strong></td></tr>
                                    @if(isset($balanceSheet['liability_accounts']) && count($balanceSheet['liability_accounts']) > 0)
                                        @foreach($balanceSheet['liability_accounts'] as $account)
                                            <tr>
                                                <td>{{ $account['name'] }}</td>
                                                <td class="text-end">${{ number_format($account['amount'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No liability accounts</td>
                                        </tr>
                                    @endif
                                    <tr class="table-secondary">
                                        <td>Total Liabilities</td>
                                        <td class="text-end">${{ number_format($balanceSheet['liabilities'] ?? 0, 2) }}</td>
                                    </tr>
                                    <tr class="table-light"><td colspan="2"><strong>Equity</strong></td></tr>
                                    @if(isset($balanceSheet['equity_accounts']) && count($balanceSheet['equity_accounts']) > 0)
                                        @foreach($balanceSheet['equity_accounts'] as $account)
                                            <tr>
                                                <td>{{ $account['name'] }}</td>
                                                <td class="text-end">${{ number_format($account['amount'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No equity accounts</td>
                                        </tr>
                                    @endif
                                    <tr class="table-secondary">
                                        <td>Total Equity</td>
                                        <td class="text-end">${{ number_format($balanceSheet['equity'] ?? 0, 2) }}</td>
                                    </tr>
                                    <tr class="table-dark fw-bold">
                                        <td>Total Liabilities & Equity</td>
                                        <td class="text-end">${{ number_format(($balanceSheet['liabilities'] ?? 0) + ($balanceSheet['equity'] ?? 0), 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @php
                    $difference = abs(($balanceSheet['assets'] ?? 0) - (($balanceSheet['liabilities'] ?? 0) + ($balanceSheet['equity'] ?? 0)));
                @endphp

                @if($difference > 0.01)
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Balance sheet is out of balance by ${{ number_format($difference, 2) }}
                    </div>
                @else
                    <div class="alert alert-success mt-3">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Balanced:</strong> Assets = Liabilities + Equity
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

