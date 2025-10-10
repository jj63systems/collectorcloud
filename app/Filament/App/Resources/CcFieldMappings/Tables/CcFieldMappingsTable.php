<?php

namespace App\Filament\App\Resources\CcFieldMappings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CcFieldMappingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('team.name')
                    ->label('Team')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('field_name')
                    ->label('Field')
                    ->sortable()
                    ->searchable(),


                TextInputColumn::make('display_seq')
                    ->label('Seq')
                    ->sortable(),

                TextInputColumn::make('label')
                    ->label('Label')
                    ->sortable(),


                TextColumn::make('data_type')
                    ->label('Type')
                    ->sortable(),

                TextColumn::make('max_length')
                    ->label('Max Len')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('lookup_type.name')
                    ->label('Lookup Type')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),


                ToggleColumn::make('is_required')
                    ->label('Required')
                    ->sortable(),

                ToggleColumn::make('is_searchable')
                    ->label('Searchable')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
