<?php

namespace App\Filament\App\Resources\CcLookupValues\Schemas;

use App\Models\Tenant\CcLookupType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CcLookupValueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ─────────────────────────────────────────────────────
            // SECTION: Lookup Value Details
            // ─────────────────────────────────────────────────────
            Section::make(mylabel('cc_lookup_values', 'sections.lookup_details', 'Lookup Value Details'))
                ->description(mylabel('cc_lookup_values', 'sections.lookup_details_description',
                    'Define the lookup type, code, label, and basic attributes for this value.'))
                ->schema([
                    Grid::make(2)->schema([

                        // Lookup Type
                        Select::make('type_id')
                            ->label(mylabel('cc_lookup_values', 'fields.type_id', 'Lookup Type'))
                            ->relationship('type', 'code')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive(),

                        // Code
                        TextInput::make('code')
                            ->label(mylabel('cc_lookup_values', 'fields.code', 'Code'))
                            ->required()
                            ->maxLength(50)
                            ->extraAlpineAttributes([
                                'x-on:input' => 'event.target.value = event.target.value.toUpperCase()',
                            ]),

                        // Label
                        TextInput::make('label')
                            ->label(mylabel('cc_lookup_values', 'fields.label', 'Meaning'))
                            ->required()
                            ->maxLength(255),

                        // Sort order
                        TextInput::make('sort_order')
                            ->label(mylabel('cc_lookup_values', 'fields.sort_order', 'Sort Order'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffixIcon('heroicon-o-bars-3')
                            ->required(),

                        // Enabled toggle
                        Toggle::make('enabled')
                            ->label(mylabel('cc_lookup_values', 'fields.enabled', 'Enabled'))
                            ->inline(false)
                            ->default(true)
                            ->helperText('Toggle to enable or disable this lookup value.'),
                    ]),
                ]),

            // ─────────────────────────────────────────────────────
            // SECTION: Team Assignment (conditionally visible)
            // ─────────────────────────────────────────────────────
            Section::make(mylabel('cc_lookup_values', 'sections.team_assignment', 'Team Assignment'))
                ->description(mylabel('cc_lookup_values', 'sections.team_assignment_description',
                    'Assign this value to one or more teams (for team-scoped lookup types only).'))
                ->hidden(fn(callable $get) => !self::typeIsTeamScoped($get('type_id')))
                ->schema([
                    Grid::make(1)->schema([
                        Select::make('teams')
                            ->label(mylabel('cc_lookup_values', 'fields.teams', 'Teams'))
                            ->multiple()
                            ->relationship('teams', 'name')
                            ->preload()
                            ->searchable()
                            ->helperText('Shown only for team-scoped lookup types.'),
                    ]),
                ]),
        ]);
    }

    /**
     * Determine whether the selected lookup type is team-scoped.
     */
    protected static function typeIsTeamScoped(?int $typeId): bool
    {
        if (!$typeId) {
            return false;
        }

        return (bool) CcLookupType::whereKey($typeId)->value('is_team_scoped');
    }
}
