<?php

namespace App\Filament\App\Resources\CcLookupValues\Tables;

use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class CcLookupValuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('type.name')
            ->defaultSort('sort_order')
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistColumnSearchesInSession()
            ->persistSearchInSession()
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Id')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type.name')
                    ->label('Type')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextInputColumn::make('label')
                    ->label('Label')
                    ->sortable()
                    ->disabled(fn($record) => $record->system_flag),

                Tables\Columns\TextInputColumn::make('sort_order')
                    ->label('Sort Order')
                    ->rules(['integer', 'min:0'])
                    ->inputMode('numeric')
                    ->sortable()
                    ->disabled(fn($record) => $record->system_flag),

                Tables\Columns\TextColumn::make('teams.name')
                    ->label('Teams')
                    ->badge()
                    ->separator(', '),

                Tables\Columns\IconColumn::make('system_flag')
                    ->label('System')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('enabled')
                    ->label('Enabled')
                    ->sortable()
                    ->disabled(fn($record) => $record->system_flag),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type_id')
                    ->label('Type')
                    ->relationship('type', 'name')
                    ->preload()
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
            ]);
    }
}

