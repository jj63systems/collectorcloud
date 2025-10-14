<?php

namespace App\Filament\App\Resources\CcDonors\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CcDonorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Donor Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->maxLength(255),

                TextInput::make('telephone')
                    ->maxLength(100),

                TextInput::make('address_line_1')
                    ->label('Address Line 1'),

                TextInput::make('address_line_2')
                    ->label('Address Line 2'),

                TextInput::make('city'),

                TextInput::make('county'),

                TextInput::make('postcode'),

                TextInput::make('country'),

                Textarea::make('address_old')
                    ->label('Legacy Address')
                    ->columnSpanFull(),
            ]);
    }
}
