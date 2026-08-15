<?php

namespace App\Filament\Resources\Centros\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CentroForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identificacion')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre del centro')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Select::make('tipo')
                        ->label('Tipo')
                        ->options([
                            'acopio' => 'Centro de acopio',
                            'albergue' => 'Albergue',
                        ])
                        ->default('acopio')
                        ->required(),

                    Toggle::make('activo')
                        ->label('Visible al publico')
                        ->helperText('Apagalo cuando el centro deje de recibir.')
                        ->default(true),
                ]),

            Section::make('Ubicacion')
                ->columns(2)
                ->schema([
                    TextInput::make('direccion')
                        ->label('Direccion')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    TextInput::make('ciudad')
                        ->label('Ciudad')
                        ->required()
                        ->maxLength(120),

                    TextInput::make('departamento')
                        ->label('Departamento')
                        ->required()
                        ->maxLength(120),

                    TextInput::make('latitud')
                        ->label('Latitud')
                        ->numeric()
                        ->helperText('Opcional. Se usa en la Entrega 4.'),

                    TextInput::make('longitud')
                        ->label('Longitud')
                        ->numeric()
                        ->helperText('Opcional. Se usa en la Entrega 4.'),
                ]),

            Section::make('Contacto y operacion')
                ->columns(2)
                ->schema([
                    TextInput::make('contacto_nombre')
                        ->label('Persona de contacto')
                        ->maxLength(255),

                    TextInput::make('contacto_telefono')
                        ->label('Telefono')
                        ->tel()
                        ->maxLength(40),

                    TextInput::make('horario')
                        ->label('Horario de atencion')
                        ->placeholder('Lunes a sabado, 8:00 a. m. a 5:00 p. m.')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('notas')
                        ->label('Notas para el publico')
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
