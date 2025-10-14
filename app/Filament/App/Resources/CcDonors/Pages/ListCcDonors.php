<?php

namespace App\Filament\App\Resources\CcDonors\Pages;

use App\Filament\App\Resources\CcDonors\CcDonorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCcDonors extends ListRecords
{
    protected static string $resource = CcDonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
