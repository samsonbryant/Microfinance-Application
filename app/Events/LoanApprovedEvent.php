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

class LoanApprovedEvent implements ShouldBroadcast
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
            new PrivateChannel('client.' . $this->loan->client_id),
            new PrivateChannel('branch.' . $this->loan->branch_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'loan.approved';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->loan->id,
            'loan_number' => $this->loan->loan_number,
            'amount' => $this->loan->amount,
            'status' => $this->loan->status,
            'message' => 'Your loan application has been approved!',
        ];
    }
}

