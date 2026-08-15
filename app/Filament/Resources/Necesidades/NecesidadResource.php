<?php

namespace App\Filament\Resources\Necesidades;

use App\Filament\Resources\Necesidades\Pages\CreateNecesidad;
use App\Filament\Resources\Necesidades\Pages\EditNecesidad;
use App\Filament\Resources\Necesidades\Pages\ListNecesidades;
use App\Filament\Resources\Necesidades\Schemas\NecesidadForm;
use App\Filament\Resources\Necesidades\Tables\NecesidadesTable;
use App\Models\Necesidad;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NecesidadResource extends Resource
{
    protected static ?string $model = Necesidad::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $modelLabel = 'Necesidad';

    protected static ?string $pluralModelLabel = 'Necesidades';

    protected static ?string $navigationLabel = 'Necesidades';

    // Sin esto la URL sale como /admin/necesidades/necesidads.
    protected static ?string $slug = 'necesidades';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return NecesidadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NecesidadesTable::configure($table);
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
            'index' => ListNecesidades::route('/'),
            'create' => CreateNecesidad::route('/create'),
            'edit' => EditNecesidad::route('/{record}/edit'),
        ];
    }
}
