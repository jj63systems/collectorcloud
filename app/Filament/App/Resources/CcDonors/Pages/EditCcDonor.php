<?php

namespace App\Filament\App\Resources\CcDonors\Pages;

use App\Filament\App\Resources\CcDonors\CcDonorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCcDonor extends EditRecord
{
    protected static string $resource = CcDonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
