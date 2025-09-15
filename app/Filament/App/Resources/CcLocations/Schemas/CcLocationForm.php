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


                        Select::make('type_id')
                            ->label('Type')
//                            ->relationship('type', 'label') // assumes you want to display the label
                            ->options(
                                \App\Models\Tenant\CcLookupValue::enabled()
                                    ->whereHas('type', fn($q) => $q->where('code', 'LOCATION_TYPE'))
                                    ->orderBy('sort_order')
                                    ->pluck('label', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
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
                ->collapsed(false)
                ->columnSpanFull()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('path')
                            ->label('Full Path')
                            ->disabled()
                            ->readOnly(),

                        TextInput::make('depth')
                            ->label('Depth')
                            ->disabled()
                            ->readOnly(),
                    ]),
                ]),
        ]);
    }
}
