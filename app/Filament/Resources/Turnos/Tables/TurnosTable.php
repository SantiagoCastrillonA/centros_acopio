<?php

namespace App\Filament\Resources\Turnos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TurnosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount(['inscripcionesActivas as tomados']))
            ->columns([
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('horario')
                    ->label('Hora')
                    ->state(fn ($record) => $record->horario),

                TextColumn::make('tipo_tarea')
                    ->label('Tarea')
                    ->state(fn ($record) => $record->emoji.' '.$record->tarea)
                    ->wrap(),

                TextColumn::make('centro.nombre')
                    ->label('Centro')
                    ->description(fn ($record) => $record->centro?->ciudad)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('tomados')
                    ->label('Anotados')
                    ->state(fn ($record) => $record->tomados.' de '.$record->cupos)
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($record) => $record->tomados >= $record->cupos ? 'success' : 'warning'),

                IconColumn::make('abierto')
                    ->label('Abierto')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('centro_id')
                    ->label('Centro')
                    ->relationship('centro', 'nombre')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('tipo_tarea')
                    ->label('Tarea')
                    ->options([
                        'clasificar' => 'Clasificar donaciones',
                        'cargar' => 'Cargar y descargar',
                        'cocinar' => 'Cocinar',
                        'atender' => 'Atender a las familias',
                        'aseo' => 'Aseo del centro',
                        'inventario' => 'Llevar el inventario',
                        'otro' => 'Ayuda general',
                    ]),

                Filter::make('proximos')
                    ->label('Solo los que vienen')
                    ->query(fn ($query) => $query->whereDate('fecha', '>=', now()->toDateString()))
                    ->default(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('fecha');
    }
}
