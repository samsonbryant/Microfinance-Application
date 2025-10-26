<?php

namespace App\Events;

use App\Models\RevenueEntry;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RevenueUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $revenue;

    public function __construct(RevenueEntry $revenue)
    {
        $this->revenue = $revenue;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('branch.' . $this->revenue->branch_id),
            new PrivateChannel('accounting'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'revenue.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->revenue->id,
            'status' => $this->revenue->status,
        ];
    }
}

