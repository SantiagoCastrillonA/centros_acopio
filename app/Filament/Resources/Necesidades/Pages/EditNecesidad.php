<?php

namespace App\Filament\Resources\Necesidades\Pages;

use App\Filament\Resources\Necesidades\NecesidadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNecesidad extends EditRecord
{
    protected static string $resource = NecesidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
