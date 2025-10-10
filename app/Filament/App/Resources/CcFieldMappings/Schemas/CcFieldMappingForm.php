<?php

namespace App\Filament\App\Resources\CcFieldMappings\Schemas;

use App\Models\Tenant\CcLookupType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

// ✅ use Group instead of Grid

// optional, for nice grouping

class CcFieldMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Field Mapping Details')
                ->schema([
                    Group::make()
                        ->columns(2)
                        ->schema([
                            TextInput::make('label')
                                ->label('Label')
                                ->maxLength(255),

                            Select::make('data_type')
                                ->label('Type')
                                ->options([
                                    'TEXT' => 'Text',
                                    'NUMBER' => 'Number',
                                    'DATE' => 'Date',
                                    'LOOKUP' => 'Lookup',
                                ])
                                ->required(),

                            TextInput::make('max_length')
                                ->label('Max Length')
                                ->numeric()
                                ->minValue(1)
                                ->nullable(),

                            Select::make('lookup_type_id')
                                ->label('Lookup Type')
                                ->options(fn() => CcLookupType::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->nullable(),

                            TextInput::make('display_seq')
                                ->label('Display Order')
                                ->numeric()
                                ->minValue(1)
                                ->required(),

                            Select::make('toggle_option')
                                ->label('Toggle Option')
                                ->options([
                                    'notoggle' => 'No Toggle',
                                    'toggle_shown' => 'Toggle - default shown',
                                    'toggle_not_shown' => 'Toggle - default not shown',
                                ])
                                ->required(),

                            Toggle::make('is_required')
                                ->label('Required'),

                            Toggle::make('is_searchable')
                                ->label('Searchable'),
                        ]),
                ]),
        ]);
    }
}
