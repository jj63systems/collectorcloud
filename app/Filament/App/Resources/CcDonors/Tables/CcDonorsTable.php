<?php

namespace App\Filament\App\Resources\CcDonors\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CcDonorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Donor Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('telephone')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('city')
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('country')
                    ->toggleable()
                    ->searchable(),
            ])
            ->defaultSort('name');
    }
}
