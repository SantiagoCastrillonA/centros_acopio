<?php

namespace App\Filament\Resources\Centros\Pages;

use App\Filament\Resources\Centros\CentroResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCentro extends EditRecord
{
    protected static string $resource = CentroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
