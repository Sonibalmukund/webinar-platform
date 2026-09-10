<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebinarQuestionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $webinarId,
        public string $action,
        public array $question
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('webinar.room.'.$this->webinarId)];
    }

    public function broadcastAs(): string
    {
        return 'question.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'question' => $this->question,
        ];
    }
}
