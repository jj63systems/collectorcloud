<?php

namespace App\Filament\App\Resources\CcLocations\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CcLocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->withCount('children')) // ✅ this is the right method
            ->columns([

                TextColumn::make('id')
                    ->label(mylabel('cc_locations', 'fields.id', 'Location ID'))
                    ->sortable()
                    ->searchable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->label(mylabel('cc_locations', 'fields.name', 'Location Name'))
                    ->sortable()
                    ->searchable(),

//                TextColumn::make('parent.name')
//                    ->label(mylabel('cc_locations', 'fields.parent', 'Parent Location'))
//                    ->sortable()
//                    ->toggleable(),

                TextColumn::make('path')
                    ->label(mylabel('cc_locations', 'fields.path', 'Full Path'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type.label') // Updated to show related lookup value's label
                ->label(mylabel('cc_locations', 'fields.type', 'Type'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('children_count')
                    ->label(mylabel('cc_locations', 'fields.children_count', 'Child Locations'))
                    ->sortable()
                    ->numeric()
                    ->alignEnd()
                    ->toggleable(),

//                TextColumn::make('code')
//                    ->label(mylabel('cc_locations', 'fields.code', 'Code'))
//                    ->sortable()
//                    ->toggleable(),
            ])
            ->recordActions([
                ViewAction::make(),
//                EditAction::make()->slideOver(),
//                DeleteAction::make()->slideOver()
//                    ->visible(fn(CcLocation $record) => $record->canDelete())
//                    ->before(function (CcLocation $record) {
//                        if (!$record->canDelete()) {
//                            Notification::make()
//                                ->title(mylabel('cc_locations', 'notifications.cannot_delete', 'This location has child locations and cannot be deleted.'))
//                                ->danger()
//                                ->send();
//                            throw new Halt();
//                        }
//                    }),
//                Action::make('activities')->url(fn($record) => CcLocationResource::getUrl('activities',
//                    ['record' => $record])),
                // ...
            ]);
    }
}
