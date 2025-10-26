@extends('layouts.app')

@section('content')
<div class="container-fluid" style="font-family: 'Inter', sans-serif;">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-plus-circle text-danger"></i> Create Expense
            </h2>
            <p class="text-muted mb-0">Record a new expense transaction</p>
        </div>
        <a href="{{ route('accounting.expenses.index') }}" class="btn btn-secondary" style="border-radius: 8px;">
            <i class="fas fa-arrow-left"></i> Back to Expenses
        </a>
    </div>

    <!-- Expense Form Card -->
    <div class="row">
        <div class="col-md-8">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0 fw-semibold">Expense Details</h5>
                </div>
                <div class="card-body">
                    @livewire('expense-form-live')
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="col-md-4">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-header bg-primary text-white border-0 pt-4">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-info-circle"></i> Quick Guide
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-semibold">How to record an expense:</h6>
                    <ol class="small">
                        <li>Select the transaction date</li>
                        <li>Choose the expense account (e.g., Rent, Salaries)</li>
                        <li>Enter the amount</li>
                        <li>Select payment method:
                            <ul>
                                <li><strong>Cash</strong> - Paid from cash on hand</li>
                                <li><strong>Cheque</strong> - Select bank and enter cheque number</li>
                                <li><strong>Bank Transfer</strong> - Select bank account</li>
                                <li><strong>Mobile Money</strong> - Select mobile money account</li>
                            </ul>
                        </li>
                        <li>Enter payee name (who received the payment)</li>
                        <li>Add a detailed description</li>
                        <li>Submit for approval</li>
                    </ol>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <small>
                            <strong>Note:</strong> The expense will be pending until approved. 
                            Once approved and posted, it will automatically create journal entries 
                            and update account balances.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Recent Expenses -->
            <div class="card mt-3" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="card-header bg-white border-0 pt-4">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-clock text-primary"></i> Recent Expenses
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @php
                            $recentExpenses = \App\Models\Expense::with('account')
                                ->latest()
                                ->take(5)
                                ->get();
                        @endphp
                        
                        @forelse($recentExpenses as $expense)
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="small">
                                    <strong>{{ $expense->account->name ?? 'N/A' }}</strong>
                                    <br>
                                    <span class="text-muted">{{ $expense->transaction_date->format('M d, Y') }}</span>
                                </div>
                                <span class="badge bg-danger">${{ number_format($expense->amount, 2) }}</span>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted small mb-0">No recent expenses</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

