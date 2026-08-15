<?php

namespace App\Filament\Resources\Necesidades\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NecesidadesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Agrupado por centro y plegado: un coordinador trabaja un punto
            // a la vez, y ver el mismo nombre repetido veinte veces solo
            // gasta pantalla.
            ->groups([
                Group::make('centro.nombre')
                    ->label('Centro')
                    ->titlePrefixedWithLabel(false)
                    ->collapsible(),
            ])
            ->defaultGroup('centro.nombre')
            ->groupingSettingsInDropdownOnDesktop()
            ->columns([
                TextColumn::make('centro.nombre')
                    ->label('Centro')
                    ->searchable()
                    ->sortable()
                    // Dentro del grupo el nombre del centro ya esta en la
                    // cabecera: la columna se puede encender si hace falta.
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('item.nombre')
                    ->label('Insumo')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->item?->unidad),

                TextColumn::make('prioridad')
                    ->label('Prioridad')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'alta' => 'danger',
                        'media' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                TextColumn::make('cantidad_requerida')
                    ->label('Requerido')
                    ->alignEnd(),

                TextColumn::make('cantidad_cubierta')
                    ->label('Recibido')
                    ->alignEnd(),

                TextColumn::make('pendiente')
                    ->label('Falta')
                    ->state(fn ($record) => $record->pendiente)
                    ->alignEnd()
                    ->color(fn ($record) => $record->pendiente > 0 ? 'danger' : 'success')
                    ->weight('bold'),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('centro_id')
                    ->label('Centro')
                    ->relationship('centro', 'nombre')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('prioridad')
                    ->label('Prioridad')
                    ->options([
                        'alta' => 'Alta',
                        'media' => 'Media',
                        'baja' => 'Baja',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            // Dentro de cada centro, primero lo urgente y lo que mas lejos
            // esta de cubrirse. FIELD() y CAST son de MySQL, el motor elegido.
            ->modifyQueryUsing(fn ($query) => $query
                ->orderByRaw("FIELD(prioridad, 'alta', 'media', 'baja')")
                ->orderByRaw('GREATEST(CAST(cantidad_requerida AS SIGNED) - CAST(cantidad_cubierta AS SIGNED), 0) DESC'));
    }
}
