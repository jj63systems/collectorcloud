<?php

namespace App\Filament\App\Resources\CcTeams\Schemas;


use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CcTeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Team Name')
                ->required()
                ->maxLength(255),
        ]);
    }
}
