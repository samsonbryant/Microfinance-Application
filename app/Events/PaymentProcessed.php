<?php

namespace App\Events;

use App\Models\LoanRepayment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $repayment;

    public function __construct(LoanRepayment $repayment)
    {
        $this->repayment = $repayment;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('client.' . $this->repayment->loan->client_id),
            new PrivateChannel('branch.' . $this->repayment->loan->branch_id),
            new PrivateChannel('accounting'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'payment.processed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->repayment->id,
            'loan_id' => $this->repayment->loan_id,
            'amount' => $this->repayment->amount,
            'principal' => $this->repayment->principal_amount,
            'interest' => $this->repayment->interest_amount,
        ];
    }
}

