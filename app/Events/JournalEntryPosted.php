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

class JournalEntryPosted implements ShouldBroadcast
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
        return 'journal-entry.posted';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->journalEntry->id,
            'total_debits' => $this->journalEntry->total_debits,
            'total_credits' => $this->journalEntry->total_credits,
        ];
    }
}

