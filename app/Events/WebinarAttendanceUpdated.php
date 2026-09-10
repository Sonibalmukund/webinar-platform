<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebinarAttendanceUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public int $webinarId, public int $userId, public int $liveViewers, public int $watchSeconds, public string $state, public ?string $userName = null) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('webinar.manage.'.$this->webinarId), new PrivateChannel('webinar.room.'.$this->webinarId)];
    }

    public function broadcastAs(): string
    {
        return 'attendance.updated';
    }

    public function broadcastWith(): array
    {
        return ['webinar_id' => $this->webinarId, 'user_id' => $this->userId, 'live_viewers' => $this->liveViewers, 'watch_seconds' => $this->watchSeconds, 'state' => $this->state, 'user_name' => $this->userName];
    }
}
