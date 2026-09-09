<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationForm extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'require_login' => 'boolean'];
    }

    public function webinar(): BelongsTo
    {
        return $this->belongsTo(Webinar::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(RegistrationField::class)->orderBy('display_order');
    }
}
