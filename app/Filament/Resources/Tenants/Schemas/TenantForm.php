<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                TextInput::make('domain')->required(),
                TextInput::make('database')->required(),

                Fieldset::make('Initial User')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])
                    ->schema([
                        TextInput::make('user_name')
                            ->label('User Name')
                            ->required(),

                        TextInput::make('user_email')
                            ->label('User Email')
                            ->email()
                            ->required(),

                        TextInput::make('user_password')
                            ->label('User Password')
                            ->password()
                            ->required()
                            ->minLength(8),
                    ]),
            ]);
    }
}
