<?php

namespace App\Filament\App\Resources\CcItems\Schemas;

use App\Models\Tenant\CcFieldGroup;
use App\Models\Tenant\CcFieldMapping;
use App\Models\Tenant\CcLookupValue;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CcItemForm
{
    public static function configure(Schema $schema): Schema
    {
        $components = [];

        // Standard fields section
        $components[] = Section::make('Item Details')
            ->schema([
                TextInput::make('name')
                    ->label('Item Name')
                    ->required()
                    ->maxLength(20),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->maxLength(500),
            ])
            ->columns(1)
            ->collapsible();

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

                foreach ($fields->sortBy('display_seq') as $field) {
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
                        ->required($field->is_required);

                    $fieldComponents[] = $component;
                }

                $groupLabel = $groupId !== 'ungrouped'
                    ? (CcFieldGroup::find($groupId)?->name ?? 'Fields')
                    : 'Other Fields';

                $components[] = Section::make($groupLabel)
                    ->schema($fieldComponents)
                    ->columns(1)
                    ->collapsible();
            }
        }

        return $schema->components([
            Group::make()
                ->schema($components)
                ->columns(1),
        ]);
    }
}
