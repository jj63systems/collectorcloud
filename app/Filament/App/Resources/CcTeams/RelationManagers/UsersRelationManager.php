<?php

namespace App\Filament\App\Resources\CcTeams\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users'; // must match CcTeam model relationship

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Full Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email Address')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('pivot.created_at')
                    ->label('Joined At')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
//                    ->searchable(['name', 'email'])
                    ->label('Add User'),
            ])
            ->recordActions([
                DetachAction::make(),
//                DeleteAction::make(), // optional — removes user entirely, not just from team
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
//                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
