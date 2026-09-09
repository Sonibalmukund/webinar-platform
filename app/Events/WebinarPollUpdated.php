<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebinarPollUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public int $webinarId, public int $pollId, public array $options) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('webinar.room.'.$this->webinarId)];
    }

    public function broadcastAs(): string
    {
        return 'poll.updated';
    }

    public function broadcastWith(): array
    {
        return ['poll_id' => $this->pollId, 'options' => $this->options];
    }
}
