<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Http;
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
                $coordenadas = static::coordenadasValidas(
                    (float) $coincidencias[1],
                    (float) $coincidencias[2]
                );

                if ($coordenadas !== null) {
                    return $coordenadas;
                }
            }
        }

        return null;
    }

    /** Descarta lo que no cae dentro del planeta. */
    private static function coordenadasValidas(float $latitud, float $longitud): ?array
    {
        if (abs($latitud) > 90 || abs($longitud) > 180) {
            return null;
        }

        return ['latitud' => $latitud, 'longitud' => $longitud];
    }

    /**
     * Coordenadas dentro de la pagina de Google Maps.
     *
     * La direccion final de un enlace corto ya no las trae escritas: Google
     * dejo de ponerlas en la ruta. Si estan en el HTML, y en dos sitios que
     * apuntan a la ficha en concreto y no al centro de la ventana.
     */
    private static function coordenadasDesdeHtml(string $html): ?array
    {
        // La imagen de vista previa que Google arma para esa ficha.
        // El separador viaja escapado de varias formas segun donde aparezca.
        $separador = '(?:%2C|,|\\\\u002[cC])';
        $igual = '(?:=|\\\\u003[dD])';

        if (preg_match('/staticmap\?center'.$igual.'(-?\d{1,3}\.\d+)'.$separador.'(-?\d{1,3}\.\d+)/', $html, $c)) {
            return static::coordenadasValidas((float) $c[1], (float) $c[2]);
        }

        // Formato de los enlaces para incrustar. Ojo al orden: !2d es la
        // longitud y !3d la latitud, al reves de como se leen.
        if (preg_match('/!2d(-?\d{1,3}\.\d+)!3d(-?\d{1,3}\.\d+)/', $html, $c)) {
            return static::coordenadasValidas((float) $c[2], (float) $c[1]);
        }

        return null;
    }

    /**
     * Dominios que solo guardan un codigo y responden con un redirect.
     *
     * Es lo que entrega el boton "Compartir" de Google Maps en el celular,
     * que es justo por donde pasa un coordinador. Sin resolverlos, el centro
     * queda sin coordenadas: no sale en el mapa de la portada ni entra en el
     * orden por cercania.
     */
    public const DOMINIOS_ACORTADOS = [
        'goo.gl',
        'maps.app.goo.gl',
        'ul.waze.com',
    ];

    /**
     * Coordenadas de un enlace, resolviendolo contra el servidor si hace
     * falta. Devuelve null si el enlace no las lleva o si no hubo respuesta:
     * el centro se queda con el enlace y sin punto en el mapa, que es el
     * comportamiento de antes.
     */
    public static function coordenadasDesdeEnlace(?string $url): ?array
    {
        $directas = static::coordenadasDesdeUrl($url);

        if ($directas !== null) {
            return $directas;
        }

        if (! static::esUrlDeMapaValida($url) || blank($url)) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if (! in_array($host, self::DOMINIOS_ACORTADOS, true)) {
            return null;
        }

        try {
            // La peticion sale hacia un dominio de la lista cerrada y nada
            // mas. El tope de tiempo es corto a proposito: esto ocurre
            // mientras el coordinador espera con el formulario abierto.
            $respuesta = Http::timeout(6)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; CentrosDeAcopio/1.0)'])
                ->get($url);
        } catch (\Throwable) {
            return null;
        }

        // Guzzle sigue los redirect solo: la direccion final es la larga, y
        // esa si lleva las coordenadas escritas.
        $final = static::coordenadasDesdeUrl((string) $respuesta->effectiveUri());

        // Google ya no las pone en la direccion final, pero si dentro de la
        // pagina.
        return $final ?? static::coordenadasDesdeHtml($respuesta->body());
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
