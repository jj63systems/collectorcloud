<?php

namespace App\Filament\App\Resources\CcItemStages\Pages;

use App\Filament\App\Resources\CcItemStages\CcItemStageResource;
use Filament\Resources\Pages\ListRecords;

class ListCcItemStages extends ListRecords
{
    protected static string $resource = CcItemStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            CreateAction::make(),
        ];
    }
}
