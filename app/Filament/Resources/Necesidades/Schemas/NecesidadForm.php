<?php

namespace App\Filament\Resources\Necesidades\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class NecesidadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('centro_id')
                ->label('Centro')
                ->relationship('centro', 'nombre')
                ->searchable()
                ->preload()
                ->required(),

            Select::make('item_id')
                ->label('Insumo')
                ->relationship('item', 'nombre', fn ($query) => $query->where('activo', true))
                ->searchable()
                ->preload()
                ->required()
                // La tabla tiene unique(centro_id, item_id). Sin esta regla,
                // repetir un insumo sale como excepcion de MySQL.
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('centro_id', $get('centro_id')),
                )
                ->validationMessages([
                    'unique' => 'Ese insumo ya está publicado en este centro. Edite la necesidad que ya existe.',
                ])
                ->helperText('Si el insumo no esta en la lista, crealo primero en Insumos.'),

            // Las columnas son unsignedInteger: el tope evita el mismo
            // "out of range" que dan las coordenadas sin limites.
            TextInput::make('cantidad_requerida')
                ->label('Cantidad requerida')
                ->numeric()
                ->integer()
                ->minValue(0)
                ->maxValue(4294967295)
                ->required()
                ->default(0),

            TextInput::make('cantidad_cubierta')
                ->label('Cantidad ya recibida')
                ->numeric()
                ->integer()
                ->minValue(0)
                ->maxValue(4294967295)
                ->required()
                ->default(0),

            Select::make('prioridad')
                ->label('Prioridad')
                ->options([
                    'alta' => 'Alta',
                    'media' => 'Media',
                    'baja' => 'Baja',
                ])
                ->default('media')
                ->required(),

            TextInput::make('nota')
                ->label('Nota')
                ->placeholder('Solo productos nuevos y sellados')
                ->maxLength(255)
                ->columnSpanFull(),
        ])->columns(2);
    }
}
