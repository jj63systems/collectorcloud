<?php

namespace App\Filament\App\Resources\CcItems\Pages;

use App\Filament\App\Resources\CcItems\CcItemResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCcItem extends CreateRecord
{
    protected static string $resource = CcItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Auth::user()->current_team_id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
