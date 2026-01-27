<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    public const ROLE_UNIVERSITY_ADMIN = 'UNIVERSITY_ADMIN';

    public const ROLE_DORM_ADMIN = 'DORM_ADMIN';

    public const ROLE_STUDENT = 'STUDENT';

    public const ROLE_SUPER_ADMIN = 'SUPER_ADMIN';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'university_id',
        'is_active',
        'frozen_at',
        'ui_theme',
        'ui_sidebar_collapsed',
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
            'is_active' => 'boolean',
            'frozen_at' => 'datetime',
            'ui_sidebar_collapsed' => 'boolean',
        ];
    }

    public function isFrozen(): bool
    {
        return !$this->is_active || $this->frozen_at !== null;
    }

    public function isActive(): bool
    {
        return !$this->isFrozen();
    }

    public function dormAdmin()
    {
        return $this->hasOne(DormAdmin::class);
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }
}
