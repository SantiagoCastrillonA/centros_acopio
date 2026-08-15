<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Centro extends Model
{
    protected $table = 'centros';

    protected $fillable = [
        'nombre',
        'slug',
        'tipo',
        'direccion',
        'ciudad',
        'departamento',
        'latitud',
        'longitud',
        'contacto_nombre',
        'contacto_telefono',
        'horario',
        'notas',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'latitud' => 'decimal:7',
            'longitud' => 'decimal:7',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Centro $centro) {
            if (blank($centro->slug)) {
                $centro->slug = static::slugUnico($centro->nombre, $centro->ciudad);
            }
        });
    }

    public static function slugUnico(string $nombre, ?string $ciudad = null): string
    {
        $base = Str::slug(trim($nombre.' '.$ciudad));
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function necesidades(): HasMany
    {
        return $this->hasMany(Necesidad::class);
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
