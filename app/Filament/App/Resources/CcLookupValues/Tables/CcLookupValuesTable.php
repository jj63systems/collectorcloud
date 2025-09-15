<?php

namespace App\Filament\App\Resources\CcLookupValues\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class CcLookupValuesTable
{

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('type_id')
            ->columns([
                Tables\Columns\TextColumn::make('type.code')
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
                    ->inputMode('numeric')->sortable()
                    ->disabled(fn($record) => $record->system_flag),

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
                    ->relationship('type', 'code')
                    ->searchable(),
            ]);
    }


}
