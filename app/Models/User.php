<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
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
        'password',
        'first_name',
        'last_name',
        'auth_type',

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

    public function getRoleLabelAttribute()
    {
        return match (strtolower(trim($this->role))) {
            'usuario' => 'Usuario',
            'super administrador', 'admin super' => 'Admin Super',
            'administrador de inventario', 'admin inventario' => 'Admin Inventario',
            'administrador de facilidad', 'admin facilidades' => 'Admin Facilidades',
            'administrador de mercado', 'admin mercado' => 'Admin Mercado',
            default => 'Usuario',
        };
    }

    public function getRoleBadgeClassAttribute()
    {
        return match ($this->role_label) {
            'Usuario' => 'bg-primary-subtle text-primary-emphasis',
            'Admin Super' => 'bg-danger-subtle text-danger-emphasis',
            'Admin Inventario' => 'bg-success-subtle text-success-emphasis',
            'Admin Facilidades' => 'bg-info-subtle text-info-emphasis',
            'Admin Mercado' => 'bg-warning-subtle text-warning-emphasis',
            default => 'bg-secondary-subtle text-secondary-emphasis',
        };
    }

    public function isAdmin()
    {
        return in_array($this->role, ['market_admin', 'super_admin', 'user']);
    }
}
