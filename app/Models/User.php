<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_FINANCE = 'finance';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_OWNER = 'owner';
    public const ROLE_PRODUCTION = 'production';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'password',
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

    public static function staffRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_FINANCE,
            self::ROLE_PRODUCTION,
            self::ROLE_OWNER,
            self::ROLE_MANAGER,
        ];
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
