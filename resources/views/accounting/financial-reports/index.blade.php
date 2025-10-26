@extends('layouts.app')

@section('title', 'Financial Reports - Microbook-G5')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-chart-bar me-2"></i>Financial Reports - Microbook-G5</h4>
                <div class="btn-group">
                    <button type="button" class="btn btn-success" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </button>
                    <button type="button" class="btn btn-primary" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </button>
                    <a href="{{ route('accounting.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter me-2"></i>Report Filters
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('accounting.financial-reports') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="{{ $startDate }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="{{ $endDate }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="basis" class="form-label">Accounting Basis</label>
                                    <select class="form-select" id="basis" name="basis">
                                        <option value="cash" {{ $basis === 'cash' ? 'selected' : '' }}>Cash Basis</option>
                                        <option value="accrual" {{ $basis === 'accrual' ? 'selected' : '' }}>Accrual Basis</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-2"></i>Generate Reports
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="reportTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profit-loss-tab" data-bs-toggle="tab" 
                                    data-bs-target="#profit-loss" type="button" role="tab">
                                <i class="fas fa-chart-line me-2"></i>Profit & Loss
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="balance-sheet-tab" data-bs-toggle="tab" 
                                    data-bs-target="#balance-sheet" type="button" role="tab">
                                <i class="fas fa-balance-scale me-2"></i>Balance Sheet
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="trial-balance-tab" data-bs-toggle="tab" 
                                    data-bs-target="#trial-balance" type="button" role="tab">
                                <i class="fas fa-calculator me-2"></i>Trial Balance
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="reportTabsContent">
                        <!-- Profit & Loss Statement -->
                        <div class="tab-pane fade show active" id="profit-loss" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Profit & Loss Statement</h5>
                                <div class="text-muted">
                                    Period: {{ \Carbon\Carbon::parse($profitLoss['period']['start_date'])->format('M d, Y') }} - 
                                    {{ \Carbon\Carbon::parse($profitLoss['period']['end_date'])->format('M d, Y') }}
                                    <br>
                                    <small>Basis: {{ ucfirst($profitLoss['period']['basis']) }}</small>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Revenue</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($profitLoss['revenue'] as $revenue)
                                            <tr>
                                                <td>{{ $revenue['account']->name }}</td>
                                                <td class="text-end">{{ $revenue['formatted_amount'] }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-success">
                                            <td><strong>Total Revenue</strong></td>
                                            <td class="text-end"><strong>{{ $profitLoss['formatted_totals']['total_revenue'] }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Expenses</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($profitLoss['expenses'] as $expense)
                                            <tr>
                                                <td>{{ $expense['account']->name }}</td>
                                                <td class="text-end">{{ $expense['formatted_amount'] }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-danger">
                                            <td><strong>Total Expenses</strong></td>
                                            <td class="text-end"><strong>{{ $profitLoss['formatted_totals']['total_expenses'] }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr class="table-{{ $profitLoss['totals']['net_income'] >= 0 ? 'success' : 'danger' }}">
                                            <td><strong>Net Income (Loss)</strong></td>
                                            <td class="text-end"><strong>{{ $profitLoss['formatted_totals']['net_income'] }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Balance Sheet -->
                        <div class="tab-pane fade" id="balance-sheet" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Balance Sheet</h5>
                                <div class="text-muted">
                                    As of: {{ \Carbon\Carbon::parse($balanceSheet['as_of_date'])->format('M d, Y') }}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">ASSETS</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tbody>
                                                @foreach($balanceSheet['assets'] as $category => $accounts)
                                                    @if($accounts->count() > 0)
                                                        <tr class="table-light">
                                                            <td colspan="2"><strong>{{ ucwords(str_replace('_', ' ', $category)) }}</strong></td>
                                                        </tr>
                                                        @foreach($accounts as $account)
                                                            <tr>
                                                                <td>{{ $account['account']->name }}</td>
                                                                <td class="text-end">{{ $account['formatted_balance'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                                <tr class="table-primary">
                                                    <td><strong>TOTAL ASSETS</strong></td>
                                                    <td class="text-end"><strong>{{ $balanceSheet['formatted_totals']['total_assets'] }}</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-warning">LIABILITIES & EQUITY</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tbody>
                                                @foreach($balanceSheet['liabilities'] as $category => $accounts)
                                                    @if($accounts->count() > 0)
                                                        <tr class="table-light">
                                                            <td colspan="2"><strong>{{ ucwords(str_replace('_', ' ', $category)) }}</strong></td>
                                                        </tr>
                                                        @foreach($accounts as $account)
                                                            <tr>
                                                                <td>{{ $account['account']->name }}</td>
                                                                <td class="text-end">{{ $account['formatted_balance'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                                <tr class="table-warning">
                                                    <td><strong>TOTAL LIABILITIES</strong></td>
                                                    <td class="text-end"><strong>{{ $balanceSheet['formatted_totals']['total_liabilities'] }}</strong></td>
                                                </tr>
                                                
                                                @foreach($balanceSheet['equity'] as $category => $accounts)
                                                    @if($accounts->count() > 0)
                                                        <tr class="table-light">
                                                            <td colspan="2"><strong>{{ ucwords(str_replace('_', ' ', $category)) }}</strong></td>
                                                        </tr>
                                                        @foreach($accounts as $account)
                                                            <tr>
                                                                <td>{{ $account['account']->name }}</td>
                                                                <td class="text-end">{{ $account['formatted_balance'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                                <tr class="table-info">
                                                    <td><strong>TOTAL EQUITY</strong></td>
                                                    <td class="text-end"><strong>{{ $balanceSheet['formatted_totals']['total_equity'] }}</strong></td>
                                                </tr>
                                                <tr class="table-success">
                                                    <td><strong>TOTAL LIABILITIES & EQUITY</strong></td>
                                                    <td class="text-end"><strong>{{ $balanceSheet['formatted_totals']['total_liabilities_equity'] }}</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            @if($balanceSheet['is_balanced'])
                                <div class="alert alert-success mt-3">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Balance Sheet is Balanced!</strong> Total Assets = Total Liabilities + Equity
                                </div>
                            @else
                                <div class="alert alert-danger mt-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Balance Sheet is NOT Balanced!</strong> Please review the entries.
                                </div>
                            @endif
                        </div>

                        <!-- Trial Balance -->
                        <div class="tab-pane fade" id="trial-balance" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Trial Balance</h5>
                                <div class="text-muted">
                                    As of: {{ \Carbon\Carbon::parse($trialBalance['as_of_date'])->format('M d, Y') }}
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Account</th>
                                            <th class="text-end">Debit</th>
                                            <th class="text-end">Credit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trialBalance['entries'] as $entry)
                                            <tr>
                                                <td>
                                                    <strong>{{ $entry['account']->code }}</strong> - {{ $entry['account']->name }}
                                                    <br><small class="text-muted">{{ ucfirst($entry['account']->type) }}</small>
                                                </td>
                                                <td class="text-end">
                                                    @if($entry['debit'] > 0)
                                                        ${{ number_format($entry['debit'], 2) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if($entry['credit'] > 0)
                                                        ${{ number_format($entry['credit'], 2) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-primary">
                                            <td><strong>TOTALS</strong></td>
                                            <td class="text-end"><strong>{{ $trialBalance['formatted_totals']['total_debits'] }}</strong></td>
                                            <td class="text-end"><strong>{{ $trialBalance['formatted_totals']['total_credits'] }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            @if($trialBalance['is_balanced'])
                                <div class="alert alert-success mt-3">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Trial Balance is Balanced!</strong> Total Debits = Total Credits
                                </div>
                            @else
                                <div class="alert alert-danger mt-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Trial Balance is NOT Balanced!</strong> Please review the entries.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function exportToPDF() {
    // Implementation for PDF export
    alert('PDF export functionality will be implemented');
}

function exportToExcel() {
    // Implementation for Excel export
    alert('Excel export functionality will be implemented');
}
</script>
@endsection
