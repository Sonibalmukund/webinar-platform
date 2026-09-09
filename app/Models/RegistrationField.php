<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationField extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['validation_rules' => 'array', 'is_required' => 'boolean', 'is_enabled' => 'boolean', 'login_enabled' => 'boolean'];
    }

    public function conditionField(): BelongsTo
    {
        return $this->belongsTo(self::class, 'condition_field_id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(RegistrationForm::class, 'registration_form_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(RegistrationFieldOption::class)->orderBy('display_order');
    }
}
