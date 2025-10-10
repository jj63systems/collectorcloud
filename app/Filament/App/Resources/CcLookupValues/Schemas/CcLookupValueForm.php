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

            // ─────────────────────────────────────────────────────────
            // SECTION: Lookup Value Details
            // ─────────────────────────────────────────────────────────
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

                        // Colour
                        Select::make('color')
                            ->label(mylabel('cc_lookup_values', 'fields.color', 'Colour'))
                            ->options(self::colorOptions())
                            ->nullable()
                            ->native(false)
                            ->allowHtml()
                            ->searchable(),

                        // Enabled toggle
                        Toggle::make('enabled')
                            ->label(mylabel('cc_lookup_values', 'fields.enabled', 'Enabled'))
                            ->inline(false)
                            ->default(true)
                            ->helperText('Toggle to enable or disable this lookup value.'),
                    ]),
                ]),

            // ─────────────────────────────────────────────────────────
            // SECTION: Team Assignment (conditionally visible)
            // ─────────────────────────────────────────────────────────
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

    /**
     * Theme colour options with colour dot previews.
     */
    protected static function colorOptions(): array
    {
        return [
            'gray' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-gray-500"></span>Gray</div>',
            'red' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-red-500"></span>Red</div>',
            'orange' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-orange-500"></span>Orange</div>',
            'amber' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-amber-500"></span>Amber</div>',
            'lime' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-lime-500"></span>Lime</div>',
            'green' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-green-500"></span>Green</div>',
            'emerald' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-emerald-500"></span>Emerald</div>',
            'teal' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-teal-500"></span>Teal</div>',
            'cyan' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-cyan-500"></span>Cyan</div>',
            'blue' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-blue-500"></span>Blue</div>',
            'sky' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-sky-500"></span>Sky</div>',
            'indigo' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-indigo-500"></span>Indigo</div>',
            'purple' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-purple-500"></span>Purple</div>',
            'pink' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-pink-500"></span>Pink</div>',
            'rose' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-rose-500"></span>Rose</div>',
            'slate' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-slate-500"></span>Slate</div>',
            'zinc' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-zinc-500"></span>Zinc</div>',
        ];
    }
}
