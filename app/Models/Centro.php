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
        'mapa_url',
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

    /**
     * Dominios de mapas aceptados en mapa_url.
     *
     * La lista es cerrada a proposito: ese enlace termina como boton en una
     * pagina publica, y aceptar cualquier direccion la convertiria en un
     * lugar comodo para colgar enlaces a otra cosa.
     */
    public const DOMINIOS_DE_MAPAS = [
        'google.com',
        'www.google.com',
        'maps.google.com',
        'goo.gl',
        'maps.app.goo.gl',
        'openstreetmap.org',
        'www.openstreetmap.org',
        'osm.org',
        'waze.com',
        'www.waze.com',
        'ul.waze.com',
    ];

    public static function esUrlDeMapaValida(?string $url): bool
    {
        if (blank($url)) {
            return true;
        }

        $host = parse_url($url, PHP_URL_HOST);
        $esquema = parse_url($url, PHP_URL_SCHEME);

        return in_array($esquema, ['http', 'https'], true)
            && in_array(strtolower((string) $host), self::DOMINIOS_DE_MAPAS, true);
    }

    /**
     * Saca latitud y longitud de un enlace de mapa cuando el enlace las
     * lleva escritas. Los enlaces cortos (maps.app.goo.gl) no las traen:
     * ahi devuelve null y el centro se queda solo con el enlace.
     */
    public static function coordenadasDesdeUrl(?string $url): ?array
    {
        if (blank($url)) {
            return null;
        }

        $patrones = [
            '/@(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/',        // google: /@4.53,-75.68,17z
            '/!3d(-?\d{1,3}\.\d+)!4d(-?\d{1,3}\.\d+)/',    // google: place
            '/[?&](?:q|query|ll|daddr)=(-?\d{1,3}\.\d+),\s*(-?\d{1,3}\.\d+)/', // q=4.53,-75.68
            '/[?&]mlat=(-?\d{1,3}\.\d+)&mlon=(-?\d{1,3}\.\d+)/',           // openstreetmap
        ];

        foreach ($patrones as $patron) {
            if (preg_match($patron, $url, $coincidencias)) {
                $latitud = (float) $coincidencias[1];
                $longitud = (float) $coincidencias[2];

                if (abs($latitud) <= 90 && abs($longitud) <= 180) {
                    return ['latitud' => $latitud, 'longitud' => $longitud];
                }
            }
        }

        return null;
    }

    /**
     * A donde mandar al donante para llegar. Prefiere el enlace que pego el
     * coordinador; si no hay, arma uno con las coordenadas.
     */
    public function getComoLlegarUrlAttribute(): ?string
    {
        if (filled($this->mapa_url)) {
            return $this->mapa_url;
        }

        if (filled($this->latitud) && filled($this->longitud)) {
            return 'https://www.openstreetmap.org/?mlat='.$this->latitud.'&mlon='.$this->longitud
                .'#map=17/'.$this->latitud.'/'.$this->longitud;
        }

        return null;
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
