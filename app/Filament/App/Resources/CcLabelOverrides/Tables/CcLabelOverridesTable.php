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
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\SelectColumn::make('resource_id')
                    ->label('Resource')
                    ->options(fn() => CcResource::pluck('name', 'id'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\SelectColumn::make('team_id')
                    ->label('Team')
                    ->options(fn() => CcTeam::pluck('name', 'id'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('locale')
                    ->label('Locale')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextInputColumn::make('value')
                    ->label('Value')
                    ->sortable()
                    ->searchable()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('resource_id')
                    ->label('Resource')
                    ->options(fn() => CcResource::pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('team_id')
                    ->label('Team')
                    ->options(fn() => CcTeam::pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('locale')
                    ->label('Locale')
                    ->options([
                        'en' => 'English',
                        'fr' => 'French',
                        'de' => 'German',
                    ]),
            ])
            ->defaultSort('resource_id')
            ->paginated([25, 50, 100]);
    }
}
