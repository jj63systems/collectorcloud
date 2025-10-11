<?php

namespace App\Filament\App\Resources\CcFieldGroups\Pages;

use App\Filament\App\Resources\CcFieldGroups\CcFieldGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCcFieldGroup extends CreateRecord
{
    protected static string $resource = CcFieldGroupResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}


