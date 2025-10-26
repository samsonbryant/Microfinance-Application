<?php

namespace App\Events;

use App\Models\Expense;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpensePosted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $expense;

    public function __construct(Expense $expense)
    {
        $this->expense = $expense;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('branch.' . $this->expense->branch_id),
            new PrivateChannel('accounting'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'expense.posted';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->expense->id,
            'amount' => $this->expense->amount,
            'account_id' => $this->expense->account_id,
        ];
    }
}

