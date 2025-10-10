<?php

namespace App\Filament\App\Resources\CcFieldMappings\Pages;

use App\Filament\App\Resources\CcFieldMappings\CcFieldMappingResource;
use Filament\Resources\Pages\EditRecord;

class EditCcFieldMapping extends EditRecord
{
    protected static string $resource = CcFieldMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
