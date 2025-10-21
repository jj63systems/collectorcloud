<?php

namespace App\Filament\App\Resources\CcItemStages\Pages;

use App\Filament\App\Resources\CcItemStages\CcItemStageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCcItemStage extends CreateRecord
{
    protected static string $resource = CcItemStageResource::class;

    public static function canCreate(): bool
    {
        return false;
    }

}

