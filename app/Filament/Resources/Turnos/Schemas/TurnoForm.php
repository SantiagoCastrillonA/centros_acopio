<?php

namespace App\Filament\Resources\Turnos\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TurnoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Turno')
                ->columns(2)
                ->schema([
                    Select::make('centro_id')
                        ->label('Centro')
                        ->relationship('centro', 'nombre')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),

                    Select::make('tipo_tarea')
                        ->label('Tarea')
                        ->options([
                            'clasificar' => '📦 Clasificar donaciones',
                            'cargar' => '💪 Cargar y descargar',
                            'cocinar' => '🍲 Cocinar',
                            'atender' => '🤝 Atender a las familias',
                            'aseo' => '🧹 Aseo del centro',
                            'inventario' => '📋 Llevar el inventario',
                            'otro' => '🙌 Ayuda general',
                        ])
                        ->default('clasificar')
                        ->required(),

                    TextInput::make('cupos')
                        ->label('Cupos')
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->maxValue(500)
                        ->default(5)
                        ->required()
                        ->helperText('Cuántos voluntarios caben en este turno.'),

                    DatePicker::make('fecha')
                        ->label('Fecha')
                        ->native(false)
                        ->minDate(now()->startOfDay())
                        ->default(now())
                        ->required(),

                    Toggle::make('abierto')
                        ->label('Recibe voluntarios')
                        ->helperText('Apáguelo para cerrar el turno sin borrarlo.')
                        ->default(true),

                    TimePicker::make('hora_inicio')
                        ->label('Desde')
                        ->seconds(false)
                        ->default('08:00')
                        ->required(),

                    TimePicker::make('hora_fin')
                        ->label('Hasta')
                        ->seconds(false)
                        ->default('12:00')
                        ->required()
                        ->after('hora_inicio'),

                    TextInput::make('nota')
                        ->label('Nota para el voluntario')
                        ->placeholder('Traer guantes y botas si tiene')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
