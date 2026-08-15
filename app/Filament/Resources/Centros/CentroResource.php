<?php

namespace App\Filament\Resources\Centros;

use App\Filament\Resources\Centros\Pages\CreateCentro;
use App\Filament\Resources\Centros\Pages\EditCentro;
use App\Filament\Resources\Centros\Pages\ListCentros;
use App\Filament\Resources\Centros\Schemas\CentroForm;
use App\Filament\Resources\Centros\Tables\CentrosTable;
use App\Models\Centro;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CentroResource extends Resource
{
    protected static ?string $model = Centro::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $modelLabel = 'Centro';

    protected static ?string $pluralModelLabel = 'Centros';

    protected static ?string $navigationLabel = 'Centros';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return CentroForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CentrosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCentros::route('/'),
            'create' => CreateCentro::route('/create'),
            'edit' => EditCentro::route('/{record}/edit'),
        ];
    }
}
