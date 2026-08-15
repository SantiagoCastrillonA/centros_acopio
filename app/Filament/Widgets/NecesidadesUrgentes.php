<?php

namespace App\Filament\Widgets;

use App\Models\Necesidad;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Que falta, donde, y cuanto. Ordenado por lo que mas lejos esta de
 * cubrirse, no por fecha: el coordinador necesita saber a que centro
 * mandar el proximo camion.
 */
class NecesidadesUrgentes extends TableWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('🚨 Lo que más falta ahora')
            ->description('Solo centros visibles al público, con lo pendiente primero.')
            // FIELD() y CAST son de MySQL, que es el motor elegido. El orden
            // por faltante no puede salir del accesor: ese vive en PHP.
            ->query(
                Necesidad::query()
                    ->with(['centro', 'item'])
                    ->whereRaw('cantidad_cubierta < cantidad_requerida')
                    ->whereHas('centro', fn ($query) => $query->where('activo', true))
                    ->orderByRaw("FIELD(prioridad, 'alta', 'media', 'baja')")
                    ->orderByRaw('GREATEST(CAST(cantidad_requerida AS SIGNED) - CAST(cantidad_cubierta AS SIGNED), 0) DESC')
            )
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50])
            ->columns([
                TextColumn::make('item.nombre')
                    ->label('Insumo')
                    ->formatStateUsing(fn ($state, $record) => $record->item->emoji.' '.$state)
                    ->description(fn ($record) => $record->item->unidad)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('centro.nombre')
                    ->label('Centro')
                    ->description(fn ($record) => $record->centro->ciudad)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('prioridad')
                    ->label('Prioridad')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'alta' => 'danger',
                        'media' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'alta' => '🚨 Urgente',
                        'media' => '⚠️ Necesario',
                        default => '🕗 Cuando se pueda',
                    }),

                TextColumn::make('faltante')
                    ->label('Faltan')
                    ->state(fn ($record) => number_format($record->pendiente, 0, ',', '.'))
                    ->alignEnd()
                    ->weight('bold')
                    ->color('danger'),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                Action::make('rapido')
                    ->label('Actualizar')
                    ->icon(Heroicon::OutlinedBolt)
                    ->color('success')
                    ->url(fn ($record) => route('rapido', $record->centro)),
            ])
            ->emptyStateHeading('Nada pendiente por ahora')
            ->emptyStateDescription('Cuando un centro publique lo que necesita, aparece aquí.')
            ->emptyStateIcon(Heroicon::OutlinedCheckCircle);
    }
}
