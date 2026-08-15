<?php

namespace App\Filament\Resources\Items\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->required(),
                TextInput::make('unidad')
                    ->required(),
                Select::make('categoria')
                    ->options([
            'alimento' => 'Alimento',
            'agua' => 'Agua',
            'higiene' => 'Higiene',
            'habitat' => 'Habitat',
            'salud' => 'Salud',
            'bebe' => 'Bebe',
            'herramienta' => 'Herramienta',
            'otro' => 'Otro',
        ])
                    ->default('otro')
                    ->required(),
                Toggle::make('activo')
                    ->required(),
            ]);
    }
}
