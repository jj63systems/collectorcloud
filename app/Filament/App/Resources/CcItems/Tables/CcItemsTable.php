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


//            TextColumn::make('test_color2')
//                ->label('Test color2')
//                ->badge()
//                ->state(fn() => 'Oil Pressure')
//                ->color('teal'),
//

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

            // Preload all lookup values with color and label
            $lookupValues = CcLookupValue::query()
                ->where('enabled', true)
                ->get()
                ->keyBy('id');

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
                    $column
                        ->label($field->label)
                        ->formatStateUsing(function ($record) use ($lookupValues, $field) {
                            $value = $record->{$field->field_name};
                            $lookup = $lookupValues[$value] ?? null;

                            if (!$lookup) {
                                return e($value);
                            }

                            $colorClass = $lookup->color ? 'fi-color-'.$lookup->color : 'fi-color-gray';

                            return <<<HTML
                                <span class="fi-badge {$colorClass}">
                                    {$lookup->label}
                                </span>
                            HTML;
                        })
                        ->html();
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
