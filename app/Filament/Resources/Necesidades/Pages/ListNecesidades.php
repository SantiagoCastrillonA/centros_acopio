<?php

namespace App\Filament\Resources\Necesidades\Pages;

use App\Filament\Resources\Necesidades\NecesidadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNecesidades extends ListRecords
{
    protected static string $resource = NecesidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
