<?php

namespace App\Filament\App\Resources\CcLookupValues\Pages;

use App\Filament\App\Resources\CcLookupValues\CcLookupValueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCcLookupValues extends ListRecords
{
    protected static string $resource = CcLookupValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
