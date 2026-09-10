<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebinarChatMessageVoted implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $webinarId,
        public int $messageId,
        public int $votesCount,
        public ?int $userId = null
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('webinar.chat.'.$this->webinarId)];
    }

    public function broadcastAs(): string
    {
        return 'chat.voted';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'votes_count' => $this->votesCount,
            'user_id' => $this->userId,
        ];
    }
}
