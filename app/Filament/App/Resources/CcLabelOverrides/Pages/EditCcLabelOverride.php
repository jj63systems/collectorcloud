<?php

namespace App\Filament\App\Resources\CcLabelOverrides\Pages;

use App\Filament\App\Resources\CcLabelOverrides\CcLabelOverrideResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCcLabelOverride extends EditRecord
{
    protected static string $resource = CcLabelOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
