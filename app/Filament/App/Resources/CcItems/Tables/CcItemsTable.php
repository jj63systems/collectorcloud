<?php

namespace App\Filament\App\Resources\CcItems\Tables;

use App\Models\Tenant\CcFieldMapping;
use App\Models\Tenant\CcLookupValue;
use App\Models\Tenant\CcTeam;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CcItemsTable
{
    public static function configure(Table $table): Table
    {
        $columns = [
            TextColumn::make('id')
                ->label('Id')
                ->sortable()
                ->searchable(),

            TextColumn::make('name')
                ->label('Item Name')
                ->sortable()
                ->searchable(),

            TextColumn::make('team.name')
                ->label('Team')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('accessioned_at')
                ->label('Accessioned')
                ->date('d/m/Y')
                ->sortable()
                ->toggleable(),

            TextColumn::make('accessionedBy.name')
                ->label('Accessioned By')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('description')
                ->label('Description')
                ->limit(50)
                ->wrap()
                ->toggleable(isToggledHiddenByDefault: true),
        ];

        // TEMP: use first team to simulate current context
        $teamId = CcTeam::query()->value('id');

        if ($teamId) {
            $fieldMappings = CcFieldMapping::forTeam($teamId);

            // Preload all lookup values into a cache keyed by ID only
            $lookupCache = CcLookupValue::query()
                ->where('enabled', true)
                ->pluck('label', 'id');

            foreach ($fieldMappings as $field) {
                $column = TextColumn::make($field->field_name)
                    ->label($field->label)
                    ->wrap()
                    ->toggleable();

                if ($field->data_type === 'DATE') {
                    $column->formatStateUsing(function ($state) {
                        if (!$state) {
                            return null;
                        }

                        try {
                            return Carbon::parse($state)->format('d/m/Y');
                        } catch (\Throwable) {
                            return $state;
                        }
                    });
                }

                if ($field->data_type === 'LOOKUP') {
                    $column->formatStateUsing(function ($state) use ($lookupCache) {
                        if (!$state) {
                            return null;
                        }

                        return $lookupCache[$state] ?? $state;
                    });
                }

                $columns[] = $column;
            }
        }

        return $table
            ->columns($columns)
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
