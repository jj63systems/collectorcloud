<?php

namespace App\Filament\App\Resources\CcTeams\Pages;

use App\Filament\App\Resources\CcTeams\CcTeamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCcTeams extends ListRecords
{
    protected static string $resource = CcTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
