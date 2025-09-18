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
                    ->label(mylabel('resources.cc_locations.fields.id'))
                    ->sortable()
                    ->searchable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->label(mylabel('resources.cc_locations.fields.name'))
                    ->sortable()
                    ->searchable(),

//                TextColumn::make('parent.name')
//                    ->label(label('resources.cc_locations.fields.parent'))
//                    ->sortable()
//                    ->toggleable(),

                TextColumn::make('path')
                    ->label(mylabel('resources.cc_locations.fields.path'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type.label') // Updated to show related lookup value's label
                ->label(mylabel('resources.cc_locations.fields.type'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('children_count')
                    ->label(mylabel('resources.cc_locations.fields.children_count'))
                    ->sortable()
                    ->numeric()
                    ->alignEnd()
                    ->toggleable(),

//                TextColumn::make('code')
//                    ->label(label('resources.cc_locations.fields.code'))
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
//                                ->title(label('resources.cc_locations.notifications.cannot_delete'))
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
