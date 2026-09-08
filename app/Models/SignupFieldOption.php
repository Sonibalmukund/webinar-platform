<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SignupFieldOption extends Model { protected $guarded = []; public function field(): BelongsTo { return $this->belongsTo(SignupField::class, 'signup_field_id'); } }
