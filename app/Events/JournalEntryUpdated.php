<?php

namespace App\Events;

use App\Models\JournalEntry;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JournalEntryUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $journalEntry;

    public function __construct(JournalEntry $journalEntry)
    {
        $this->journalEntry = $journalEntry;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('branch.' . $this->journalEntry->branch_id),
            new PrivateChannel('accounting'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'journal-entry.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->journalEntry->id,
            'status' => $this->journalEntry->status,
        ];
    }
}

