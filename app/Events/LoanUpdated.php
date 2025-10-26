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

class LoanUpdated implements ShouldBroadcast
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
        ];
    }

    public function broadcastAs(): string
    {
        return 'loan.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->loan->id,
            'status' => $this->loan->status,
            'outstanding_balance' => $this->loan->outstanding_balance,
        ];
    }
}

