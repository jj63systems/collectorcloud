<?php

namespace App\Filament\App\Resources\CcLabelOverrides\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class CcLabelOverridesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([


                Tables\Columns\TextColumn::make('locale')
                    ->label('Locale')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextInputColumn::make('value')
                    ->label('Value')
                    ->searchable()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('locale')
                    ->options([
                        'en' => 'English',
                        // add other locales if needed
                    ]),
            ]);
    }
}
