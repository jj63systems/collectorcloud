<?php

namespace App\Filament\App\Resources\CcLocations\Schemas;

use App\Models\Tenant\CcLocation;
use App\Models\Tenant\CcLookupValue;
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

            Section::make(mylabel('cc_locations', 'sections.location_details', 'Location Details'))
                ->description(mylabel('cc_locations', 'sections.location_details_description',
                    'Enter the name and type of this location.'))
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label(mylabel('cc_locations', 'fields.name', 'Location Name'))
                            ->required()
                            ->maxLength(255),

                        Select::make('type_id')
                            ->label(mylabel('cc_locations', 'fields.type', 'Type'))
                            ->options(
                                CcLookupValue::enabled()
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

            Section::make(mylabel('cc_locations', 'sections.hierarchy', 'Hierarchy'))
                ->description(mylabel('cc_locations', 'sections.hierarchy_description',
                    'Set the parent location to build the hierarchy.'))
                ->schema([
                    Grid::make(1)->schema([
                        Select::make('parent_id')
                            ->label(mylabel('cc_locations', 'fields.parent', 'Parent Location'))
                            ->options(fn() => CcLocation::all()->pluck('path', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
                ]),

            Section::make(mylabel('cc_locations', 'sections.system_fields', 'System Fields'))
                ->description(mylabel('cc_locations', 'sections.system_fields_description',
                    'These values are automatically calculated.'))
                ->collapsed(false)
                ->columnSpanFull()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('path')
                            ->label(mylabel('cc_locations', 'fields.path', 'Full Path'))
                            ->disabled()
                            ->readOnly(),

                        TextInput::make('depth')
                            ->label(mylabel('cc_locations', 'fields.depth', 'Depth'))
                            ->disabled()
                            ->readOnly(),
                    ]),
                ]),
        ]);
    }
}
