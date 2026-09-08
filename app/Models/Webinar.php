<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Webinar extends Model
{
    protected $guarded = [];
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function registrationForm(): HasOne { return $this->hasOne(RegistrationForm::class); }
    public function registrations(): HasMany { return $this->hasMany(Registration::class); }
    public function polls(): HasMany { return $this->hasMany(Poll::class); }
    public function speakers(): BelongsToMany { return $this->belongsToMany(Speaker::class)->withPivot(['role','display_order'])->orderBy('speaker_webinar.display_order'); }

    protected function casts(): array
    {
        return ['starts_at'=>'datetime','ends_at'=>'datetime','published_at'=>'datetime','registration_deadline'=>'datetime','settings'=>'array','chat_enabled'=>'boolean','qa_enabled'=>'boolean','polls_enabled'=>'boolean','comments_enabled'=>'boolean','feedback_enabled'=>'boolean','auto_approve'=>'boolean'];
    }

    public function opensAt(): ?\Illuminate\Support\Carbon { return $this->starts_at?->copy()->subMinutes($this->early_entry_minutes ?: 30); }
    public function canEnter(): bool { return $this->status==='live' || ($this->opensAt() && now()->greaterThanOrEqualTo($this->opensAt()) && (!$this->ends_at || now()->lessThanOrEqualTo($this->ends_at))); }
}
