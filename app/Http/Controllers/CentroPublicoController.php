<?php

namespace App\Http\Controllers;

use App\Models\Centro;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

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
     *
     * Filtra por departamento y ciudad, y ordena por cercania cuando el
     * navegador entrega coordenadas. Todo por parametros en la URL: el
     * filtro funciona con JavaScript apagado.
     */
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'departamento' => ['nullable', 'string', 'max:120'],
            'ciudad' => ['nullable', 'string', 'max:120'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $lat = $filtros['lat'] ?? null;
        $lng = $filtros['lng'] ?? null;
        $cerca = $lat !== null && $lng !== null;

        $consulta = Centro::query()
            ->activos()
            ->with(['necesidades' => function (HasMany $query) {
                $query->where('prioridad', 'alta')
                    ->whereRaw(self::FALTANTE.' > 0')
                    ->with('item')
                    ->orderByRaw(self::FALTANTE.' DESC');
            }])
            ->when($filtros['departamento'] ?? null, fn ($query, $valor) => $query->where('departamento', $valor))
            ->when($filtros['ciudad'] ?? null, fn ($query, $valor) => $query->where('ciudad', $valor));

        if ($cerca) {
            // ST_Distance_Sphere devuelve metros. POINT recibe primero la
            // longitud: es el eje X. Los centros sin coordenadas dan NULL y
            // se van al final en vez de desaparecer de la lista.
            $consulta
                ->selectRaw('centros.*')
                ->selectRaw(
                    'ST_Distance_Sphere(POINT(longitud, latitud), POINT(?, ?)) AS distancia_m',
                    [$lng, $lat]
                )
                ->orderByRaw('distancia_m IS NULL')
                ->orderBy('distancia_m');
        } else {
            $consulta->orderBy('departamento')->orderBy('ciudad')->orderBy('nombre');
        }

        $centros = $consulta->get();

        return view('publico.index', [
            'centros' => $centros,
            'puntos' => $this->puntosDelMapa($centros),
            'cerca' => $cerca,
            'filtros' => [
                'departamento' => $filtros['departamento'] ?? null,
                'ciudad' => $filtros['ciudad'] ?? null,
            ],
            'lugares' => $this->lugaresConCentros(),
        ]);
    }

    /**
     * Ciudades agrupadas por departamento, solo donde hay centros activos.
     * Sirve para llenar el selector sin ofrecer opciones que no dan
     * resultados.
     */
    private function lugaresConCentros(): array
    {
        return Centro::activos()
            ->select('departamento', 'ciudad')
            ->distinct()
            ->orderBy('departamento')
            ->orderBy('ciudad')
            ->get()
            ->groupBy('departamento')
            ->map(fn ($grupo) => $grupo->pluck('ciudad')->all())
            ->all();
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

        // withCount y no un conteo por turno: con 20 turnos, cargar las
        // inscripciones de cada uno son 20 consultas de mas.
        $turnos = $centro->turnos()
            ->disponibles()
            ->withCount(['inscripcionesActivas as tomados'])
            ->get();

        return view('publico.centro', [
            'centro' => $centro,
            'turnos' => $turnos,
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
