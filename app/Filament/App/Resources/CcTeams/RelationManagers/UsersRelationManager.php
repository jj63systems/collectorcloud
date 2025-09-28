<?php

namespace App\Filament\App\Resources\CcTeams\RelationManagers;

use App\Models\Tenant\TenantUser;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    // Must exactly match CcTeam::users()
    protected static string $relationship = 'users';
    protected static ?string $title = 'Team Members';   // 👤 More descriptive
    protected static string|null|\BackedEnum $icon = 'heroicon-o-user-group'; // add icon

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
            ->headerActions([
                AttachAction::make()
                    ->label('Attach User')
                    ->recordSelect(function ($select) {
                        return $select
                            ->label('User')
                            ->searchable() // ✅ basic search box
                            ->getSearchResultsUsing(fn(string $search) => TenantUser::query()
                                ->where(function ($query) use ($search) {
                                    $query->where('name', 'ILIKE', "%{$search}%")
                                        ->orWhere('email', 'ILIKE', "%{$search}%");
                                })
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn($user) => [
                                    $user->id => "{$user->name} — {$user->email}",
                                ])
                            )
                            ->getOptionLabelUsing(function ($value): ?string {
                                $user = TenantUser::find($value);
                                return $user ? "{$user->name} — {$user->email}" : null;
                            });
                    }),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
//                BulkActionGroup::make([
//                    DetachBulkAction::make(),
//                ]),
            ]);
    }
}
