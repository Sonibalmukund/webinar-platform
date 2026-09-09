<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebinarRoomUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public int $webinarId, public string $change, public array $state = []) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('webinar.room.'.$this->webinarId), new Channel('webinar.public.'.$this->webinarId)];
    }

    public function broadcastAs(): string
    {
        return 'room.updated';
    }

    public function broadcastWith(): array
    {
        return ['change' => $this->change, 'state' => $this->state];
    }
}
