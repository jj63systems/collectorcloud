<?php

namespace App\Filament\App\Resources\CcItemStages\Schemas;

use App\Models\Tenant\CcFieldGroup;
use App\Models\Tenant\CcFieldMapping;
use App\Models\Tenant\CcLookupValue;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CcItemStageForm
{
    public static function configure(Schema $schema): Schema
    {
        $components = [];

        // --- Stage Item Overview ---
        $components[] = Section::make('🟡 Staged Item Overview')
            ->schema([
                TextInput::make('name')
                    ->label('Item Name')
                    ->helperText('Imported field — not yet reviewed')
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->maxLength(500),

                TextInput::make('data_load_id')
                    ->label('Data Load Run')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('id')
                    ->label('Row ID')
                    ->disabled()
                    ->dehydrated(false),
            ])
            ->columns(2)
            ->collapsible();

        // --- Dynamic team-specific mapped fields ---
        $teamId = Auth::user()?->current_team_id;

        if ($teamId) {
            $fieldMappings = CcFieldMapping::query()
                ->where('team_id', $teamId)
                ->whereNotNull('label')
                ->orderBy('display_seq')
                ->get();

            $grouped = $fieldMappings
                ->groupBy(fn($field) => $field->field_group_id ?? 'ungrouped')
                ->sortBy(function ($fields, $groupId) {
                    if ($groupId === 'ungrouped') {
                        return PHP_INT_MAX;
                    }
                    return CcFieldGroup::find($groupId)?->display_seq ?? PHP_INT_MAX;
                });

            foreach ($grouped as $groupId => $fields) {
                $fieldComponents = [];

                $group = $groupId !== 'ungrouped'
                    ? CcFieldGroup::find($groupId)
                    : null;

                $isProtected = $group?->is_protected ?? false;
                $groupLabel = $group?->name ?? 'Other Imported Fields';

                foreach ($fields->sortBy('display_seq') as $field) {
                    if ($isProtected) {
                        // --- Protected: read-only (likely system or key fields) ---
                        $component = TextEntry::make($field->field_name)
                            ->label($field->label)
                            ->helperText('Locked field');
                    } else {
                        // --- Editable field mappings ---
                        $component = match ($field->data_type) {
                            'TEXT' => TextInput::make($field->field_name)
                                ->maxLength($field->max_length ?? 255),

                            'NUMBER' => TextInput::make($field->field_name)
                                ->numeric(),

                            'DATE' => DatePicker::make($field->field_name),

                            'LOOKUP' => Select::make($field->field_name)
                                ->options(function () use ($field) {
                                    if (!$field->lookup_type_id) {
                                        return [];
                                    }

                                    return CcLookupValue::query()
                                        ->where('type_id', $field->lookup_type_id)
                                        ->where('enabled', true)
                                        ->orderBy('sort_order')
                                        ->pluck('label', 'id')
                                        ->toArray();
                                }),

                            default => TextInput::make($field->field_name),
                        };

                        $component = $component
                            ->label($field->label)
                            ->required(false) // staged data should not enforce validation yet
                            ->hint('Imported value');
                    }

                    $fieldComponents[] = $component;
                }

                $components[] = Section::make($groupLabel)
                    ->schema($fieldComponents)
                    ->columns(2)
                    ->collapsible();
            }
        }

        // --- Error information section ---
        $components[] = Section::make('⚠ Data Validation Summary')
            ->schema([
                Textarea::make('data_error_summary')
                    ->label('Validation Notes')
                    ->rows(3)
                    ->disabled()
                    ->visible(fn($record) => filled($record?->data_error_summary)),
            ])
            ->columns(1)
            ->collapsible();

        return $schema->components([
            Group::make()
                ->schema($components)
                ->columns(1),
        ]);
    }
}
