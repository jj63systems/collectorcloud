<?php

namespace App\Filament\App\Resources\CcLabelOverrides\Schemas;

use App\Models\Tenant\CcResource;
use App\Models\Tenant\CcTeam;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class CcLabelOverrideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Override Context')
                ->description('Choose the resource, team, and locale this override applies to.')
                ->schema([
                    Select::make('resource_id')
                        ->label('Resource')
                        ->options(fn() => CcResource::all()->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive(),

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

            Section::make('Label Override')
                ->description('Specify which key you are overriding and its new value.')
                ->schema([
                    Radio::make('key_type')
                        ->label('Key Type')
                        ->options([
                            'sections' => 'Sections',
                            'fields' => 'Fields',
                        ])
                        ->inline()
                        ->reactive(),

                    Select::make('key')
                        ->label('Key')
                        ->options(function (callable $get) {
                            $resourceId = $get('resource_id');
                            $keyType = $get('key_type');

                            if (!$resourceId) {
                                return [];
                            }

                            $resource = CcResource::find($resourceId);
                            if (!$resource || !$resource->code) {
                                return [];
                            }

                            $allKeys = config("label_keys.{$resource->code}", []);

                            if ($keyType) {
                                $allKeys = collect($allKeys)
                                    ->filter(fn($label, $key) => str_starts_with($key, "{$keyType}."))
                                    ->toArray();
                            }

                            return collect($allKeys)->mapWithKeys(function ($label, $key) {
                                $parts = explode('.', $key, 2);
                                $prefix = ucfirst($parts[0] ?? 'Other');
                                return [$key => "[{$prefix}] {$label}"];
                            })->toArray();
                        })
                        ->required()
                        ->reactive()
                        ->searchable()
                        ->preload()
                        ->rules([
                            fn(callable $get, callable $context) => Rule::unique('cc_label_overrides')
                                ->ignore($context('record')?->id)
                                ->where(function ($query) use ($get) {
                                    return $query
                                        ->where('resource_id', $get('resource_id'))
                                        ->where('locale', $get('locale'))
                                        ->when($get('team_id'), function ($q) use ($get) {
                                            $q->where('team_id', $get('team_id'));
                                        }, function ($q) {
                                            $q->whereNull('team_id');
                                        });
                                }),
                        ]),

                    TextInput::make('value')
                        ->label('Value')
                        ->placeholder('e.g. Custom Path Label')
                        ->required()
                        ->maxLength(255),
                ]),
        ]);
    }
}
