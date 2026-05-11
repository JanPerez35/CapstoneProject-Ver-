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
        'status',
        'role',
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
            'super administrador' => 'Super Administrador',
            'administrador de inventario' => 'Administrador de Inventario',
            'administrador de facilidad', 'administrador de instalaciones' => 'Administrador de Instalaciones',
            'administrador de mercado' => 'Administrador de Mercado',
            default => 'Usuario',
        };
    }

    public function getRoleBadgeClassAttribute()
    {
        return match ($this->role_label) {
            'Usuario' => 'badge-user',
            'Super Administrador' => 'badge-super-admin',
            'Administrador de Inventario' => 'badge-inventory-admin',
            'Administrador de Instalaciones' => 'badge-facility-admin',
            'Administrador de Mercado' => 'badge-market-admin',
            default => 'badge-user',
        };
    }

    public function isAdmin()
    {
        return in_array($this->role, ['market_admin', 'super_admin', 'user']);
    }

    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'seller_id');
    }
}
