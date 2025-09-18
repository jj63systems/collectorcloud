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

            Section::make(mylabel('resources.cc_locations.sections.location_details'))
                ->description(mylabel('resources.cc_locations.sections.location_details_description'))
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label(mylabel('resources.cc_locations.fields.name'))
                            ->required()
                            ->maxLength(255),

                        Select::make('type_id')
                            ->label(mylabel('resources.cc_locations.fields.type'))
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

            Section::make(mylabel('resources.cc_locations.sections.hierarchy'))
                ->description(mylabel('resources.cc_locations.sections.hierarchy_description'))
                ->schema([
                    Grid::make(1)->schema([
                        Select::make('parent_id')
                            ->label(mylabel('resources.cc_locations.fields.parent'))
                            ->options(fn() => CcLocation::all()->pluck('path', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
                ]),

            Section::make(mylabel('resources.cc_locations.sections.system_fields'))
                ->description(mylabel('resources.cc_locations.sections.system_fields_description'))
                ->collapsed(false)
                ->columnSpanFull()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('path')
                            ->label(mylabel('resources.cc_locations.fields.path'))
                            ->disabled()
                            ->readOnly(),

                        TextInput::make('depth')
                            ->label(mylabel('resources.cc_locations.fields.depth'))
                            ->disabled()
                            ->readOnly(),
                    ]),
                ]),
        ]);
    }
}
