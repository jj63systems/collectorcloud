<?php

namespace App\Filament\App\Resources\CcItemStages\Tables;

use App\Models\Tenant\CcDataLoad;
use App\Models\Tenant\CcFieldMapping;
use App\Models\Tenant\CcLookupValue;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CcItemStagesTable
{
    public static function configure(Table $table): Table
    {

        $table->heading('🟡 Imported Items (Staging)');
        $columns = [
            TextColumn::make('id')
                ->label('Id')
                ->sortable()
                ->searchable(),


            TextColumn::make('name')
                ->label('Item Name')
                ->sortable()
                ->searchable(),

            TextColumn::make('data_load_id')
                ->label('Data Load ID')
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

        // ✅ Add filter for Data Load Run
        $filters[] = SelectFilter::make('data_load_id')
            ->label('Data Load Run')
            ->options(
                CcDataLoad::query()
                    ->where('team_id', $teamId)
                    ->orderByDesc('id')
                    ->get()
                    ->mapWithKeys(function ($d) {
                        $uploadedAt = \Carbon\Carbon::parse($d->uploaded_at)->format('Y-m-d H:i');
                        $label = "{$d->id} / {$d->filename} / {$d->worksheet_name} / {$uploadedAt}";
                        return [$d->id => $label];
                    })
                    ->toArray()
            )
            ->searchable()
            ->preload();

        // ✅ Load dynamic field mappings for current team
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
            }
        }

        return $table
//            ->model(CcItemStage::class)
            ->modifyQueryUsing(function ($query) use ($teamId) {
                $query->where('team_id', $teamId);
            })
            ->columns($columns)
            ->filters($filters)
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersTriggerAction(null)
            ->recordActions([
                EditAction::make()->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Add delete or custom bulk actions here
                ]),
            ]);
    }
}
