<?php

namespace App\Filament\App\Resources\CcLocations\Pages;

use App\Filament\App\Resources\CcLocations\CcLocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCcLocations extends ListRecords
{
    protected static string $resource = CcLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
