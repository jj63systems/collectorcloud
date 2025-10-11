<?php

namespace App\Filament\App\Resources\CcFieldMappings\Tables;

use Filament\Actions\BulkActionGroup;
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
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('field_name')
                    ->label('Field')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),


                TextInputColumn::make('label')
                    ->label('Label')
                    ->sortable()
                    ->rules(['nullable', 'string', 'max:255']),

                TextInputColumn::make('display_seq')
                    ->label('Seq')
                    ->rules(['nullable', 'integer', 'min:1'])
                    ->sortable(),

//                SelectColumn::make('data_type')
//                    ->label('Type')
//                    ->options([
//                        'TEXT' => 'Text',
//                        'NUMBER' => 'Number',
//                        'DATE' => 'Date',
//                        'LOOKUP' => 'Lookup',
//                    ])
//                    ->sortable(),

                TextColumn::make('type.name')
                    ->label('Lookup Type')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

//                TextColumn::make('toggle_option')
//                    ->label('Toggle'),


                ToggleColumn::make('is_filterable')
                    ->label('Filterable')
                    ->sortable(),

                ToggleColumn::make('is_sortable')
                    ->label('Sortable')
                    ->sortable(),

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
//                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
