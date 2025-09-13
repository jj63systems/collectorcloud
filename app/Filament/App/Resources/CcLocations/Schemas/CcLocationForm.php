<?php

namespace App\Filament\App\Resources\CcLocations\Schemas;

use App\Models\Tenant\CcLocation;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;


class CcLocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([


            TextInput::make('path')
                ->label('Full Path')
                ->readOnly(),

            Select::make('parent_id')
                ->label('Parent Location')
                ->options(fn() => CcLocation::all()->pluck('path', 'id'))
                ->searchable()
                ->preload()
                ->nullable(),

            TextInput::make('name')
                ->label('Location Name')
                ->required()
                ->maxLength(255),


            TextInput::make('type')
                ->label('Type (e.g., Room, Shelf)')
                ->maxLength(50)
                ->nullable(),
//
//            TextInput::make('code')
//                ->label('Reference Code')
//                ->maxLength(50)
//                ->nullable(),


            TextInput::make('depth')
                ->label('Depth')
                ->readOnly(),
            

        ]);
    }
}
