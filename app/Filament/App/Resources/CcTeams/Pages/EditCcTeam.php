<?php

namespace App\Filament\App\Resources\CcTeams\Pages;

use App\Filament\App\Resources\CcTeams\CcTeamResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCcTeam extends EditRecord
{
    protected static string $resource = CcTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
