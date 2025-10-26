<?php

namespace App\Events;

use App\Models\Loan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanDisbursed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $loan;

    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('branch.' . $this->loan->branch_id),
            new PrivateChannel('client.' . $this->loan->client_id),
            new PrivateChannel('accounting'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'loan.disbursed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->loan->id,
            'loan_number' => $this->loan->loan_number,
            'amount' => $this->loan->amount,
            'client_id' => $this->loan->client_id,
        ];
    }
}

