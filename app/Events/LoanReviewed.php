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

class LoanReviewed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $loan;
    public $reviewedBy;

    public function __construct(Loan $loan, $reviewedBy)
    {
        $this->loan = $loan;
        $this->reviewedBy = $reviewedBy;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('client.' . $this->loan->client_id),
            new PrivateChannel('branch.' . $this->loan->branch_id),
            new PrivateChannel('admins'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'loan.application.reviewed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->loan->id,
            'loan_number' => $this->loan->loan_number,
            'status' => $this->loan->status,
            'reviewed_by' => $this->reviewedBy,
        ];
    }
}

