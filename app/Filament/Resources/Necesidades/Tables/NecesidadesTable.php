<?php

namespace App\Filament\Resources\Necesidades\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
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
                    ->formatStateUsing(fn ($state, $record) => $record->item->emoji.' '.$state)
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->item?->unidad),

                // Editables en la misma tabla: con veinte insumos, abrir y
                // guardar un formulario por cada uno son veinte pantallas.
                SelectColumn::make('prioridad')
                    ->label('Prioridad')
                    ->options([
                        'alta' => '🚨 Urgente',
                        'media' => '⚠️ Necesario',
                        'baja' => '🕗 Cuando se pueda',
                    ])
                    ->selectablePlaceholder(false)
                    ->rules(['required', 'in:alta,media,baja']),

                TextInputColumn::make('cantidad_requerida')
                    ->label('Requerido')
                    ->type('number')
                    ->alignEnd()
                    // Las columnas son unsignedInteger: sin estos topes un
                    // numero grande o negativo revienta en MySQL.
                    ->rules(['required', 'integer', 'min:0', 'max:4294967295']),

                TextInputColumn::make('cantidad_cubierta')
                    ->label('Recibido')
                    ->type('number')
                    ->alignEnd()
                    ->rules(['required', 'integer', 'min:0', 'max:4294967295']),

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
                Action::make('rapido')
                    ->label('Actualizar en el celular')
                    ->icon(Heroicon::OutlinedBolt)
                    ->color('success')
                    ->iconButton()
                    ->url(fn ($record) => route('rapido', $record->centro)),

                EditAction::make()->iconButton(),
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
