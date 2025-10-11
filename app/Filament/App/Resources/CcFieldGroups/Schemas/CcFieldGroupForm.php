<?php

namespace App\Filament\App\Resources\CcFieldGroups\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CcFieldGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Group Name')
                ->required()
                ->maxLength(255),

            TextInput::make('display_seq')
                ->label('Display Order')
                ->numeric()
                ->default(0)
                ->required(),

            Toggle::make('is_protected')
                ->label('Protected Group')
                ->helperText('When enabled, fields in this group will be displayed as read-only.')
                ->default(false),
        ]);
    }
}
