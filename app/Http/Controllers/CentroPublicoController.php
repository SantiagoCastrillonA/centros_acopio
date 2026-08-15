<?php

namespace App\Http\Controllers;

use App\Models\Centro;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CentroPublicoController extends Controller
{
    /**
     * Orden por prioridad declarada, no alfabetico.
     * MySQL: FIELD() devuelve la posicion dentro de la lista.
     */
    private const ORDEN_PRIORIDAD = "FIELD(prioridad, 'alta', 'media', 'baja')";

    /**
     * Las cantidades son unsigned. Restarlas directo en SQL revienta cuando
     * lo cubierto supera lo requerido, asi que se castea a signed antes.
     */
    private const FALTANTE = 'GREATEST(CAST(cantidad_requerida AS SIGNED) - CAST(cantidad_cubierta AS SIGNED), 0)';

    /**
     * Portada: centros activos con lo que les falta de prioridad alta.
     */
    public function index(): View
    {
        $centros = Centro::query()
            ->activos()
            ->with(['necesidades' => function (HasMany $query) {
                $query->where('prioridad', 'alta')
                    ->whereRaw(self::FALTANTE.' > 0')
                    ->with('item')
                    ->orderByRaw(self::FALTANTE.' DESC');
            }])
            ->orderBy('departamento')
            ->orderBy('ciudad')
            ->orderBy('nombre')
            ->get();

        return view('publico.index', [
            'centros' => $centros,
            'puntos' => $this->puntosDelMapa($centros),
        ]);
    }

    /**
     * Marcadores del mapa. Solo entran los centros con coordenadas
     * cargadas: el resto sigue apareciendo en la lista.
     */
    private function puntosDelMapa(Collection $centros): array
    {
        return $centros
            ->filter(fn (Centro $centro) => filled($centro->latitud) && filled($centro->longitud))
            ->map(fn (Centro $centro) => [
                'nombre' => $centro->nombre,
                'detalle' => $centro->direccion.', '.$centro->ciudad,
                'lat' => (float) $centro->latitud,
                'lng' => (float) $centro->longitud,
                'url' => route('publico.centro', $centro),
                'urgente' => $centro->necesidades->isNotEmpty(),
            ])
            ->values()
            ->all();
    }

    /**
     * Detalle de un centro con todas sus necesidades.
     */
    public function show(Centro $centro): View
    {
        abort_unless($centro->activo, 404);

        $centro->load(['necesidades' => function (HasMany $query) {
            $query->with('item')
                ->orderByRaw(self::ORDEN_PRIORIDAD)
                ->orderByRaw(self::FALTANTE.' DESC');
        }]);

        $pendientes = $centro->necesidades->reject->cubierta;

        return view('publico.centro', [
            'centro' => $centro,
            'pendientes' => $pendientes,
            'cubiertas' => $centro->necesidades->filter->cubierta,
            'actualizado' => $centro->necesidades->max('updated_at') ?? $centro->updated_at,
            'puntos' => filled($centro->latitud) && filled($centro->longitud)
                ? [[
                    'nombre' => $centro->nombre,
                    'detalle' => $centro->direccion.', '.$centro->ciudad,
                    'lat' => (float) $centro->latitud,
                    'lng' => (float) $centro->longitud,
                    'url' => route('publico.centro', $centro),
                    'urgente' => $pendientes->where('prioridad', 'alta')->isNotEmpty(),
                ]]
                : [],
        ]);
    }
}
