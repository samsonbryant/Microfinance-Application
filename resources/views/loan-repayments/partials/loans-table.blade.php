@if($loans->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Loan #</th>
                    <th>Client</th>
                    <th>Outstanding Balance</th>
                    <th>Next Payment</th>
                    <th>Due Date</th>
                    @if($type === 'overdue')
                        <th>Days Overdue</th>
                    @endif
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loans as $loan)
                <tr class="{{ $type === 'overdue' ? 'table-danger' : ($type === 'due' ? 'table-warning' : '') }}">
                    <td>
                        <strong>{{ $loan->loan_number }}</strong>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div>
                                <div class="font-weight-bold">{{ $loan->client->full_name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $loan->client->phone ?? '' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <strong class="text-danger">${{ number_format($loan->outstanding_balance ?? 0, 2) }}</strong>
                    </td>
                    <td>
                        <strong class="text-success">${{ number_format($loan->next_payment_amount ?? 0, 2) }}</strong>
                    </td>
                    <td>
                        @if($loan->next_due_date)
                            <div>
                                <strong>{{ $loan->next_due_date->format('M d, Y') }}</strong><br>
                                <small class="text-muted">{{ $loan->next_due_date->diffForHumans() }}</small>
                            </div>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    @if($type === 'overdue')
                        <td>
                            <span class="badge bg-danger">
                                {{ $loan->next_due_date ? now()->diffInDays($loan->next_due_date) : 0 }} days
                            </span>
                        </td>
                    @endif
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-info" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('loans.payment-schedule', $loan) }}" class="btn btn-sm btn-primary" title="Payment Schedule">
                                <i class="fas fa-calendar"></i>
                            </a>
                            <a href="{{ route('loans.repayment', $loan) }}" class="btn btn-sm btn-success" title="Make Payment">
                                <i class="fas fa-dollar-sign"></i> Pay
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="table-active">
                    <th colspan="{{ $type === 'overdue' ? 2 : 2 }}">Totals:</th>
                    <th>${{ number_format($loans->sum('outstanding_balance') ?? 0, 2) }}</th>
                    <th>${{ number_format($loans->sum('next_payment_amount') ?? 0, 2) }}</th>
                    <th colspan="{{ $type === 'overdue' ? 3 : 2 }}"></th>
                </tr>
            </tfoot>
        </table>
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
        <h5 class="text-muted">No {{ ucfirst($type) }} Loans</h5>
        <p class="text-muted">
            @if($type === 'due')
                No loans are due today.
            @elseif($type === 'overdue')
                Great! No overdue loans.
            @else
                No upcoming payments in the next 30 days.
            @endif
        </p>
    </div>
@endif

