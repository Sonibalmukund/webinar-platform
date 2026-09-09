<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationFieldOption extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean'];
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(RegistrationField::class, 'registration_field_id');
    }
}
