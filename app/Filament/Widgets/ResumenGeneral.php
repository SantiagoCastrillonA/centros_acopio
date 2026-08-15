<?php

namespace App\Filament\Widgets;

use App\Models\Centro;
use App\Models\Necesidad;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

/**
 * Lo primero que ve el coordinador al entrar. Responde cuatro preguntas:
 * cuantos puntos hay abiertos, que tan urgente esta la cosa, cuanto se
 * lleva conseguido, y donde se dejo de actualizar.
 */
class ResumenGeneral extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $activos = Centro::activos()->count();
        $totales = Centro::count();

        $urgentes = Necesidad::query()
            ->where('prioridad', 'alta')
            ->whereRaw('cantidad_cubierta < cantidad_requerida')
            ->whereHas('centro', fn ($query) => $query->where('activo', true))
            ->count();

        $centrosConUrgencias = Necesidad::query()
            ->where('prioridad', 'alta')
            ->whereRaw('cantidad_cubierta < cantidad_requerida')
            ->whereHas('centro', fn ($query) => $query->where('activo', true))
            ->distinct()
            ->count('centro_id');

        // Cobertura global: se topa lo cubierto contra lo requerido para que
        // un centro que recibio de mas no infle el porcentaje de los demas.
        $sumas = Necesidad::query()
            ->whereHas('centro', fn ($query) => $query->where('activo', true))
            ->selectRaw('SUM(cantidad_requerida) AS requerido')
            ->selectRaw('SUM(LEAST(cantidad_cubierta, cantidad_requerida)) AS cubierto')
            ->first();

        $requerido = (int) ($sumas->requerido ?? 0);
        $cubierto = (int) ($sumas->cubierto ?? 0);
        $cobertura = $requerido > 0 ? (int) round($cubierto * 100 / $requerido) : 0;

        $desactualizados = Centro::activos()
            ->whereDoesntHave('necesidades', fn ($query) => $query->where('updated_at', '>=', now()->subDay()))
            ->count();

        return [
            Stat::make('🏠 Centros abiertos', $activos)
                ->description($totales > $activos ? ($totales - $activos).' apagados' : 'Todos visibles al público')
                ->color($activos > 0 ? 'success' : 'gray'),

            Stat::make('🚨 Insumos urgentes', $urgentes)
                ->description($centrosConUrgencias === 1 ? 'en 1 centro' : 'en '.$centrosConUrgencias.' centros')
                ->color($urgentes > 0 ? 'danger' : 'success'),

            Stat::make('📦 Cobertura', $cobertura.'%')
                ->description(number_format($cubierto, 0, ',', '.').' de '.number_format($requerido, 0, ',', '.').' unidades')
                ->color(match (true) {
                    $cobertura >= 80 => 'success',
                    $cobertura >= 40 => 'warning',
                    default => 'danger',
                }),

            Stat::make('🕗 Sin actualizar hoy', $desactualizados)
                ->description($desactualizados > 0 ? 'Llame antes de confiar en esos datos' : 'Todos actualizados en 24 h')
                ->color($desactualizados > 0 ? 'warning' : 'success'),
        ];
    }
}
