<?php

namespace App\Filament\App\Resources\CcLabelOverrides\Pages;

use App\Filament\App\Resources\CcLabelOverrides\CcLabelOverrideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCcLabelOverrides extends ListRecords
{
    protected static string $resource = CcLabelOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
