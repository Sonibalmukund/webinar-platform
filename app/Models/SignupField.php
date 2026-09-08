<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class SignupField extends Model { protected $guarded = []; protected function casts(): array { return ['is_required'=>'boolean','is_enabled'=>'boolean']; } public function options(): HasMany { return $this->hasMany(SignupFieldOption::class)->orderBy('display_order'); } }
