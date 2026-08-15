<?php

namespace App\Filament\Resources\Centros\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CentrosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Centro')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'acopio' => 'Acopio',
                        'albergue' => 'Albergue',
                        default => $state,
                    }),

                TextColumn::make('ciudad')
                    ->label('Ciudad')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('necesidades_count')
                    ->label('Necesidades')
                    ->counts('necesidades')
                    ->alignCenter(),

                IconColumn::make('activo')
                    ->label('Publico')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'acopio' => 'Acopio',
                        'albergue' => 'Albergue',
                    ]),

                TernaryFilter::make('activo')
                    ->label('Visible al publico'),
            ])
            ->recordActions([
                // Primera accion a proposito: es la que el coordinador usa
                // todos los dias desde el celular. Editar es lo excepcional.
                Action::make('rapido')
                    ->label('Actualizar')
                    ->icon(Heroicon::OutlinedBolt)
                    ->color('success')
                    ->url(fn ($record) => route('rapido', $record)),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nombre');
    }
}
