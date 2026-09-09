<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Speaker extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'social_links' => 'array'];
    }

    public function webinars(): BelongsToMany
    {
        return $this->belongsToMany(Webinar::class)->withPivot(['role', 'display_order'])->withTimestamps();
    }
}
