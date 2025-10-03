<?php

namespace App\Filament\App\Resources\Roles\RelationManagers;

use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';   // Role::users()
    protected static ?string $title = 'Assigned Users';
    protected static string|null|\BackedEnum $icon = 'heroicon-o-user-group';

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
                    ->label('Email')
                    ->sortable()
                    ->searchable(),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Remove Role'),
            ]);
    }
}
