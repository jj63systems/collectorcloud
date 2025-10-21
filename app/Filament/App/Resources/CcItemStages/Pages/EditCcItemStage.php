<?php

namespace App\Filament\App\Resources\CcItemStages\Pages;

use App\Filament\App\Resources\CcItemStages\CcItemStageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCcItemStage extends EditRecord
{
    protected static string $resource = CcItemStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
