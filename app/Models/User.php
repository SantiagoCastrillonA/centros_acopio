<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
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

    /**
     * Sin esto, Filament devuelve 403 en cualquier entorno que no sea local:
     * es su proteccion para que un modelo User sin reglas no quede abierto
     * en produccion.
     *
     * Aqui no hay roles ni jerarquia de permisos, y las cuentas se crean a
     * mano con make:filament-user, asi que tener cuenta es la autorizacion.
     * Cuando existan varios coordinadores por centro, esta es la funcion
     * que hay que cambiar.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
