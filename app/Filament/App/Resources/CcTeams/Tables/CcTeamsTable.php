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

                TextColumn::make('name')
                    ->label('Team Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('users_count')
                    ->counts('users') // counts() auto eager loads relationship count
                    ->alignCenter()
                    ->label('Team Users')
                    ->sortable(),

                TextColumn::make('roles_count')
                    ->counts('roles') // counts() for roles as well
                    ->alignCenter()
                    ->label('Team Roles')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
