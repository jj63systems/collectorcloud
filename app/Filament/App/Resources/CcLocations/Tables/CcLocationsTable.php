<?php

namespace App\Filament\App\Resources\CcLocations\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Actions;
use Filament\Tables\Columns\TextColumn;

class CcLocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
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

                TextColumn::make('type')
                    ->label('Type')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('path')
            ->recordActions([
//                ViewAction::make(),
                EditAction::make()->slideOver(),
                // ...
            ]);
    }
}
