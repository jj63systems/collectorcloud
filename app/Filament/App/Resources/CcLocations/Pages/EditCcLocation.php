<?php

namespace App\Filament\App\Resources\CcLocations\Pages;

use App\Filament\App\Resources\CcLocations\CcLocationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCcLocation extends EditRecord
{
    protected static string $resource = CcLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
