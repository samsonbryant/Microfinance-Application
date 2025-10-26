<?php

namespace App\Events;

use App\Models\Transfer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransferUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transfer;

    public function __construct(Transfer $transfer)
    {
        $this->transfer = $transfer;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('branch.' . $this->transfer->branch_id),
            new PrivateChannel('accounting'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'transfer.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->transfer->id,
            'status' => $this->transfer->status,
        ];
    }
}

