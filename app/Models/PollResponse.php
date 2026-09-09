<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollResponse extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean', 'voted_at' => 'datetime'];
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(PollOption::class, 'poll_option_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
