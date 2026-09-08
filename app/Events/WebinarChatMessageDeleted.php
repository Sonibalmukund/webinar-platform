<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class WebinarChatMessageDeleted implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(public int $webinarId, public int $messageId) {}
    public function broadcastOn(): array { return [new PrivateChannel('webinar.chat.'.$this->webinarId)]; }
    public function broadcastAs(): string { return 'chat.deleted'; }
    public function broadcastWith(): array { return ['id' => $this->messageId]; }
}
