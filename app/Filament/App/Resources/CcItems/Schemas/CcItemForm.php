<?php

namespace App\Filament\App\Resources\CcItems\Schemas;

use App\Models\Tenant\CcFieldMapping;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CcItemForm
{
    public static function configure(Schema $schema): Schema
    {
        $components = [
            TextInput::make('name')
                ->label('Item Name')
                ->required()
                ->maxLength(20),

            Textarea::make('description')
                ->label('Description')
                ->rows(3)
                ->maxLength(500),
        ];

        // TEMP: Use first team
        $teamId = Auth::user()?->current_team_id;

        if ($teamId) {
            $fieldMappings = CcFieldMapping::forTeam($teamId);

            foreach ($fieldMappings as $field) {
                $base = match ($field->data_type) {
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

                            return \App\Models\Tenant\CcLookupValue::query()
                                ->where('type_id', $field->lookup_type_id)
                                ->where('enabled', true)
                                ->orderBy('sort_order')
                                ->pluck('label', 'id')
                                ->toArray();
                        }),

                    default => TextInput::make($field->field_name),
                };

                // Apply label and conditional required flag
                $base = $base
                    ->label($field->label)
                    ->required($field->is_required);

                $components[] = $base;
            }
        }

        return $schema->components($components);
    }
}
