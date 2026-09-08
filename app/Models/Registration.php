<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['registered_at' => 'datetime', 'approved_at' => 'datetime', 'cancelled_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function webinar(): BelongsTo { return $this->belongsTo(Webinar::class); }
}
