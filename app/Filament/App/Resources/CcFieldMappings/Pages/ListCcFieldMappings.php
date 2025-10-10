<?php

namespace App\Filament\App\Resources\CcFieldMappings\Pages;

use App\Filament\App\Resources\CcFieldMappings\CcFieldMappingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCcFieldMappings extends ListRecords
{
    protected static string $resource = CcFieldMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
