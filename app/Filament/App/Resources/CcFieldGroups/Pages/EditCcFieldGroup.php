<?php

namespace App\Filament\App\Resources\CcFieldGroups\Pages;

use App\Filament\App\Resources\CcFieldGroups\CcFieldGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCcFieldGroup extends EditRecord
{
    protected static string $resource = CcFieldGroupResource::class;

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
