<?php

namespace App\Filament\App\Resources\CcLocations\Tables;

use App\Filament\App\Resources\CcLocations\CcLocationResource;
use App\Models\tenant\CcLocation;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
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
                    ->label('Location Id')
                    ->sortable()
                    ->searchable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->label('Location Name')
                    ->sortable()
                    ->searchable(),

//                TextColumn::make('parent.name')
//                    ->label('Parent')
//                    ->sortable()
//                    ->toggleable(),


                TextColumn::make('path')
                    ->label('Full Path')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type.label') // Updated to show related lookup value's label
                ->label('Type')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('children_count')
                    ->label('Child Locations')
                    ->sortable()
                    ->numeric()
                    ->alignEnd()
                    ->toggleable(),


//                TextColumn::make('code')
//                    ->label('Code')
//                    ->sortable()
//                    ->toggleable(),
            ])
            ->recordActions([
//                ViewAction::make(),
                EditAction::make()->slideOver(),
                DeleteAction::make()->slideOver()
                    ->visible(fn(CcLocation $record) => $record->canDelete())
                    ->before(function (CcLocation $record) {
                        if (!$record->canDelete()) {
                            Notification::make()
                                ->title('This location has child locations and cannot be deleted.')
                                ->danger()
                                ->send();
                            throw new Halt();
                        }
                    }),
                Action::make('activities')->url(fn($record) => CcLocationResource::getUrl('activities',
                    ['record' => $record])),
                // ...
            ]);
    }
}
