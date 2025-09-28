<?php

namespace App\Filament\App\Resources\CcTeams\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CcTeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')->searchable(),

                TextColumn::make('users_count')
                    ->counts('users')
                    ->alignCenter()
                    ->label('Members')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                // EditAction::make(),
            ]);
    }
}
