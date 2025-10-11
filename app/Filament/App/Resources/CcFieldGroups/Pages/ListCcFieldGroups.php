<?php

namespace App\Filament\App\Resources\CcFieldGroups\Pages;

use App\Filament\App\Resources\CcFieldGroups\CcFieldGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCcFieldGroups extends ListRecords
{
    protected static string $resource = CcFieldGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
