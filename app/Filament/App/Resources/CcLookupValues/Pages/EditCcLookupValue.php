<?php

namespace App\Filament\App\Resources\CcLookupValues\Pages;

use App\Filament\App\Resources\CcLookupValues\CcLookupValueResource;
use Filament\Resources\Pages\EditRecord;

class EditCcLookupValue extends EditRecord
{
    protected static string $resource = CcLookupValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
