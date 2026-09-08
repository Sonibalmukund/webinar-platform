<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile',
        'job_title',
        'company',
        'bio',
        'timezone',
        'status',
        'country_id',
        'state_id',
        'city_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    public function country(): BelongsTo { return $this->belongsTo(Country::class); }
    public function state(): BelongsTo { return $this->belongsTo(State::class); }
    public function city(): BelongsTo { return $this->belongsTo(City::class); }
    public function signupAnswers(): HasMany { return $this->hasMany(SignupFieldAnswer::class); }
    public function registrations(): HasMany { return $this->hasMany(Registration::class); }
    public function assignedWebinars(): BelongsToMany
    {
        return $this->belongsToMany(Webinar::class, 'user_webinar_assignments')->withPivot('assigned_by')->withTimestamps();
    }
    public function webinarPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_webinar_permissions')->withPivot(['webinar_id', 'assigned_by'])->withTimestamps();
    }
    public function canForWebinar(string $permission, Webinar|int $webinar): bool
    {
        if ($this->hasRole('super-admin')) return true;
        $webinarId = $webinar instanceof Webinar ? $webinar->id : $webinar;
        return $this->webinarPermissions()->wherePivot('webinar_id', $webinarId)->where('slug', $permission)->exists();
    }
}
