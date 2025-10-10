<?php

namespace App\Filament\App\Resources\CcItems\Schemas;

use App\Models\Tenant\CcFieldMapping;
use App\Models\Tenant\CcTeam;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
        $teamId = CcTeam::query()->value('id');

        if ($teamId) {
            $fieldMappings = CcFieldMapping::forTeam($teamId);

            foreach ($fieldMappings as $field) {
                $components[] = match ($field->data_type) {
                    'TEXT' => TextInput::make($field->field_name)
                        ->label($field->label)
                        ->maxLength($field->max_length ?? 255),

                    'NUMBER' => TextInput::make($field->field_name)
                        ->label($field->label)
                        ->numeric(),

                    'DATE' => DatePicker::make($field->field_name)
                        ->label($field->label),

                    'LOOKUP' => Select::make($field->field_name)
                        ->label($field->label)
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

                    default => TextInput::make($field->field_name)
                        ->label($field->label),
                };
            }
        }

        return $schema->components($components);
    }
}
