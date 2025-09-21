<?php

namespace App\Filament\App\Resources\CcLabelOverrides\Schemas;

use App\Models\Tenant\CcResource;
use App\Models\Tenant\CcTeam;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CcLabelOverrideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Override Context')
                ->description('Choose the resource, team, and locale this override applies to.')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('resource_id')
                            ->label('Resource')
                            ->options(fn() => CcResource::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('team_id')
                            ->label('Team')
                            ->options(fn() => CcTeam::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Leave blank for tenant-wide overrides.'),

                        TextInput::make('locale')
                            ->label('Locale')
                            ->default(app()->getLocale())
                            ->required()
                            ->maxLength(10),
                    ]),
                ]),

            Section::make('Label Override')
                ->description('Specify which key you are overriding and its new value.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('key')
                            ->label('Key')
                            ->placeholder('e.g. fields.path')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('value')
                            ->label('Value')
                            ->placeholder('e.g. Custom Path Label')
                            ->required()
                            ->maxLength(255),
                    ]),
                ]),
        ]);
    }
}
