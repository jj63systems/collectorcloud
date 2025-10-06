<?php

namespace App\Filament\App\Resources\Roles\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';   // Role::permissions()
    protected static ?string $title = 'Permissions';
    protected static string|null|\BackedEnum $icon = 'heroicon-o-lock-closed';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Permission')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('guard_name')
                    ->label('Guard')
                    ->sortable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Attach Permission')
                    ->recordSelect(function ($select) {
                        return $select
                            ->relationship('permissions',
                                'name') // explicitly tell it the relationship + display column
                            ->preload()
                            ->searchable()
                            ->placeholder('Select a permission');
                    }),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Detach'),
            ]);
    }
}
