<?php

namespace App\Filament\App\Resources\CcLocations\Schemas;

use App\Models\Tenant\CcLocation;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CcLocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Location Details')
                ->description('Enter the name and type of this location.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Location Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('type')
                            ->label('Type (e.g., Room, Shelf)')
                            ->maxLength(50)
                            ->nullable(),
                    ]),
                ]),

            Section::make('Hierarchy')
                ->description('Set the parent location to build the hierarchy.')
                ->schema([
                    Grid::make(1)->schema([
                        Select::make('parent_id')
                            ->label('Parent Location')
                            ->options(fn() => CcLocation::all()->pluck('path', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
                ]),

            Section::make('System Fields')
                ->description('These values are automatically calculated.')
                ->collapsed()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('path')
                            ->label('Full Path')
                            ->readOnly(),

                        TextInput::make('depth')
                            ->label('Depth')
                            ->readOnly(),
                    ]),
                ]),
        ]);
    }
}
