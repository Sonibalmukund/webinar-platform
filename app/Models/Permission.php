<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'module'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function webinarUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_webinar_permissions')->withPivot(['webinar_id', 'assigned_by'])->withTimestamps();
    }
}
