<?php

namespace App\Filament\App\Resources\CcFieldMappings\Pages;

use App\Filament\App\Resources\CcFieldMappings\CcFieldMappingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCcFieldMapping extends EditRecord
{
    protected static string $resource = CcFieldMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
