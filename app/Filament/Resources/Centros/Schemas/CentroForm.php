<?php

namespace App\Filament\Resources\Centros\Schemas;

use App\Models\Centro;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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

                    TextInput::make('mapa_url')
                        ->label('Enlace del mapa')
                        ->url()
                        ->maxLength(500)
                        ->columnSpanFull()
                        ->placeholder('https://maps.app.goo.gl/...')
                        ->helperText('En Google Maps: busque el sitio, toque Compartir y pegue aquí el enlace. Es el botón "Cómo llegar" que ve el donante.')
                        ->rule(fn () => function (string $atributo, $valor, callable $fallar) {
                            if (! Centro::esUrlDeMapaValida($valor)) {
                                $fallar('Pegue un enlace de Google Maps, OpenStreetMap o Waze.');
                            }
                        })
                        // Al pegar el enlace se intenta sacar latitud y
                        // longitud: son las que alimentan el mapa y el orden
                        // por cercania, y asi el coordinador no las teclea.
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                            if (blank($state)) {
                                return;
                            }

                            // Resuelve tambien los enlaces cortos de
                            // Compartir, que es lo que entrega el celular.
                            $coordenadas = Centro::coordenadasDesdeEnlace($state);

                            if ($coordenadas === null) {
                                // Sin coordenadas el centro no sale en el mapa
                                // de la portada, y eso no se nota hasta que un
                                // donante lo busca. Mejor decirlo aqui.
                                Notification::make()
                                    ->warning()
                                    ->title('El enlace no trae la ubicación')
                                    ->body('Escriba la latitud y la longitud a mano, o el centro no aparecerá en el mapa de la portada.')
                                    ->send();

                                return;
                            }

                            if (blank($get('latitud'))) {
                                $set('latitud', $coordenadas['latitud']);
                            }

                            if (blank($get('longitud'))) {
                                $set('longitud', $coordenadas['longitud']);
                            }
                        }),

                    // La columna es decimal(10,7): sin estos topes, un numero
                    // grande revienta en MySQL en vez de avisar en el campo.
                    TextInput::make('latitud')
                        ->label('Latitud')
                        ->numeric()
                        ->minValue(-90)
                        ->maxValue(90)
                        ->step('0.0000001')
                        ->placeholder('4.5339')
                        ->helperText('La rellena el enlace del mapa. Sin ella el centro no sale en el mapa de la portada.'),

                    TextInput::make('longitud')
                        ->label('Longitud')
                        ->numeric()
                        ->minValue(-180)
                        ->maxValue(180)
                        ->step('0.0000001')
                        ->placeholder('-75.6811')
                        ->helperText('La rellena el enlace del mapa. En Colombia siempre es negativa.'),
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
