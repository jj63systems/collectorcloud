<?php

namespace App\Filament\App\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('User Details')
                ->description('Enter the basic details of this user.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ]),
                ]),

            Section::make('Roles & Access')
                ->description('Control access rights and roles for this user.')
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('is_superuser')
                            ->label('Superuser')
                            ->default(false),

                        Toggle::make('is_external_user')
                            ->label('External User')
                            ->default(false),

                        Toggle::make('has_email_authentication')
                            ->label('Has Email Authentication')
                            ->default(false),
                    ]),
                ]),

            Section::make('System Fields')
                ->description('These values are automatically updated.')
                ->collapsed(false)
                ->columnSpanFull()
                ->schema([
                    Grid::make(2)->schema([
                        DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->disabled(),

                        DateTimePicker::make('created_at')
                            ->label('Created At')
                            ->disabled(),

                        DateTimePicker::make('updated_at')
                            ->label('Updated At')
                            ->disabled(),
                    ]),
                ]),
        ]);
    }
}
