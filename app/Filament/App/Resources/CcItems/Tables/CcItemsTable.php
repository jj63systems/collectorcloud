<?php

namespace App\Filament\App\Resources\CcItems\Tables;

use App\Models\Tenant\CcFieldMapping;
use App\Models\Tenant\CcItem;
use App\Models\Tenant\CcLookupValue;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

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

        $filters = [];

        $teamId = Auth::user()?->current_team_id;

        if ($teamId) {
            $fieldMappings = CcFieldMapping::forTeam($teamId);

            $lookupValues = CcLookupValue::query()
                ->where('enabled', true)
                ->get()
                ->keyBy('id');

            foreach ($fieldMappings as $field) {
                $column = TextColumn::make($field->field_name)
                    ->label($field->label)
                    ->wrap();

                // Handle toggle visibility
                switch ($field->toggle_option) {
                    case 'toggle_shown':
                        $column->toggleable(isToggledHiddenByDefault: false);
                        break;
                    case 'toggle_not_shown':
                        $column->toggleable(isToggledHiddenByDefault: true);
                        break;
                    case 'notoggle':
                    default:
                        break;
                }

                // Apply sortable if flagged
                if ($field->is_sortable) {
                    $column->sortable();
                }

                // DATE formatting
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

                // LOOKUP formatting
                if ($field->data_type === 'LOOKUP') {
                    $column->formatStateUsing(function ($record) use ($lookupValues, $field) {
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
                    })->html();
                }

                $columns[] = $column;

                // Add filter if field is marked as filterable
                if ($field->is_filterable) {
                    if ($field->data_type === 'LOOKUP') {
                        $options = CcLookupValue::query()
                            ->where('type_id', $field->lookup_type_id)
                            ->where('enabled', true)
                            ->orderBy('sort_order')
                            ->pluck('label', 'id')
                            ->toArray();

                        $filters[] = SelectFilter::make($field->field_name)
                            ->label($field->label)
                            ->options($options);
                    }

                    if (in_array($field->data_type, ['TEXT', 'NUMBER'], true)) {
                        $options = CcItem::query()
                            ->distinct()
                            ->pluck($field->field_name, $field->field_name)
                            ->filter()
                            ->toArray();

                        $filters[] = SelectFilter::make($field->field_name)
                            ->label($field->label)
                            ->searchable()
                            ->options($options);
                    }
                }
            }
        }

        return $table
            ->columns($columns)
            ->filters($filters)
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersTriggerAction(null)
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
