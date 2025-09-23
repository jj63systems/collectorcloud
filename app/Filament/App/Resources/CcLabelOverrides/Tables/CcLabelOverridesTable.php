<?php

namespace App\Filament\App\Resources\CcLabelOverrides\Tables;

use App\Models\Tenant\CcResource;
use App\Models\Tenant\CcTeam;
use Filament\Tables;
use Filament\Tables\Table;

class CcLabelOverridesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

//                Tables\Columns\SelectColumn::make('resource_id')
//                    ->label('Resource')
//                    ->options(fn() => CcResource::pluck('name', 'id')->toArray())
//                    ->sortable()
//                    ->searchable(),
//
//                Tables\Columns\SelectColumn::make('team_id')
//                    ->label('Team')
//                    ->options(fn() => CcTeam::pluck('name', 'id')->toArray())
//                    ->placeholder('No team (applies globally)')
//                    ->sortable()
//                    ->searchable(),
//
//                Tables\Columns\SelectColumn::make('locale')
//                    ->label('Locale')
//                    ->options([
//                        'en' => 'English',
//                        'fr' => 'French',
//                        'de' => 'German',
//                    ])
//                    ->placeholder('Select locale')
//                    ->sortable()
//                    ->searchable(),

                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record->resource_id) {
                            return $state;
                        }

                        $resource = CcResource::find($record->resource_id);
                        if (!$resource || !$resource->code) {
                            return $state;
                        }

                        $allKeys = config("label_keys.{$resource->code}", []);

                        return $allKeys[$state] ?? $state;
                    }),

                Tables\Columns\TextInputColumn::make('value')
                    ->label('Value')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('resource_id')
                    ->label('Resource')
                    ->options(fn() => CcResource::pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('team_id')
                    ->label('Team')
                    ->options(fn() => CcTeam::pluck('name', 'id')->toArray())
                    ->placeholder('No team (global)'),

                Tables\Filters\SelectFilter::make('locale')
                    ->label('Locale')
                    ->options([
                        'en' => 'English',
                        'fr' => 'French',
                        'de' => 'German',
                    ])
                    ->placeholder('Any locale'),
            ])
            ->defaultSort('resource_id')
            ->paginated([25, 50, 100]);
    }
}
