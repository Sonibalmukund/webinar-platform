<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollOption extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(PollResponse::class);
    }
}
