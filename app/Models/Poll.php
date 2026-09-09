<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['allow_multiple' => 'boolean', 'started_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    public function webinar(): BelongsTo
    {
        return $this->belongsTo(Webinar::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('display_order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(PollResponse::class);
    }

    public function getTypeAttribute(): string
    {
        return $this->options->contains('is_correct', true) ? 'Quiz' : 'Poll';
    }
}
