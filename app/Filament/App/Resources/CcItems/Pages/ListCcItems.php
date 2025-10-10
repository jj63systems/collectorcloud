<?php

namespace App\Filament\App\Resources\CcItems\Pages;

use App\Filament\App\Resources\CcItems\CcItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCcItems extends ListRecords
{
    protected static string $resource = CcItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
