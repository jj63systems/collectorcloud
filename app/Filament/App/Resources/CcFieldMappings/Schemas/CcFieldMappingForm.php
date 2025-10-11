<?php

namespace App\Filament\App\Resources\CcFieldMappings\Schemas;

use App\Models\Tenant\CcFieldGroup;
use App\Models\Tenant\CcLookupType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

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
                            // Group
                            Select::make('field_group_id')
                                ->label('Field Group')
                                ->options(function () {
                                    $teamId = Auth::user()?->current_team_id;

                                    return CcFieldGroup::query()
                                        ->where('team_id', $teamId)
                                        ->orderBy('display_seq')
                                        ->pluck('name', 'id');
                                })
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->helperText('Select the group this field belongs to.'),

                            TextInput::make('label')
                                ->label('Label')
                                ->maxLength(255)
                                ->required(),

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

                            Toggle::make('is_filterable')
                                ->label('Use in filter'),

                            Toggle::make('is_sortable')
                                ->label('Sortable'),

                            Toggle::make('is_required')
                                ->label('Required'),

                            Toggle::make('is_searchable')
                                ->label('Searchable'),
                        ]),
                ]),
        ]);
    }
}
