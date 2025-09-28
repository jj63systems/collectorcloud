<?php

namespace App\Filament\App\Resources\CcTeams\RelationManagers;

use App\Models\Tenant\Role;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesRelationManager extends RelationManager
{
    protected static string $relationship = 'roles'; // Must match CcTeam::roles()
    protected static ?string $title = 'Team Roles';
    protected static string|null|\BackedEnum $icon = 'heroicon-o-key';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Role Name')
                    ->searchable(),
                TextColumn::make('guard_name')
                    ->label('Guard'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Attach Role')
                    ->recordSelect(function (\Filament\Forms\Components\Select $select) {
                        return $select
                            ->preload()
                            ->options(function (callable $get) {
                                $team = $this->getOwnerRecord();

                                // Get IDs of already-attached roles
                                $attachedRoleIds = $team->roles()->pluck('roles.id');

                                // Fetch only roles not already attached
                                return Role::query()
                                    ->whereNotIn('id', $attachedRoleIds)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->placeholder('Select a role');
                    }),
            ])
            ->recordActions([
                DetachAction::make(),
            ]);
    }
}
