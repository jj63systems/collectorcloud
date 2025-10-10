<?php

namespace App\Filament\App\Resources\CcItems\Pages;

use App\Filament\App\Resources\CcItems\CcItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCcItem extends EditRecord
{
    protected static string $resource = CcItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
