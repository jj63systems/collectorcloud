<?php

namespace App\Filament\App\Resources\CcLookupValues\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CcLookupValueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1) // layout the child components into 3 columns
            ->components([
                Select::make('type_id')
                    ->label('Lookup Type')
                    ->relationship('type', 'code')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('code')
                    ->label('Code')
                    ->required()
                    ->maxLength(50)
                    ->extraAlpineAttributes([
                        'x-on:input' => 'event.target.value = event.target.value.toUpperCase()',
                    ]),

                TextInput::make('label')
                    ->label('Meaning')
                    ->required()
                    ->maxLength(255),
            ]);
    }

}
