<?php

namespace App\Filament\App\Pages;

use App\Models\Tenant\CcSetting;
use App\Models\Tenant\CcSettingGroup;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;

class SystemSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;


//
//    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
//    protected static ?string $navigationLabel = 'System Settings';
//    protected static ?string $title = 'System Settings';

    // -- TITLES AND NAV SETTINGS -----------------------------

    // Global search settings
//    protected static ?string $recordTitleAttribute = 'name';
    protected static bool $isGloballySearchable = false;
    // END global search settings

    // ✅ Appears in sidebar navigation

    protected static function getNavigationPermission(): string
    {
        return 'cc_teams.view';
    }

    protected static ?string $navigationLabel = 'System > Settings';

    // ✅ Icon in navigation (any Blade Heroicon or Lucide icon name)
//    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // ✅ Optional grouping in sidebar
    protected static string|null|\UnitEnum $navigationGroup = 'Configure';

    // ✅ Title shown on the List Records page
    protected static ?string $label = 'Setting';         // Singular
    protected static ?string $pluralLabel = 'Settings';  // Plural

    // ✅ Optional custom navigation sort
    protected static ?int $navigationSort = 110;

    // END TITLES AND NAV SETTINGS ----------------------------
    public ?array $data = [];

    public function mount(): void
    {
        $this->syncFromConfig();

        $this->content->fill(
            CcSetting::query()->pluck('setting_value', 'setting_code')->toArray()
        );
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs($this->buildTabs())
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save settings')
                ->color('primary')
                ->action(fn() => $this->save()),
        ];
    }

    protected function buildTabs(): array
    {
        return CcSettingGroup::with(['settings' => fn($q) => $q->orderBy('display_seq')])
            ->orderBy('display_seq')
            ->get()
            ->map(fn(CcSettingGroup $group) => Tab::make($group->label ?? $group->code)
                ->schema(
                    $group->settings
                        ->sortBy('display_seq')
                        ->map(fn(CcSetting $s) => $this->groupFor($s))
                        ->all()
                )
            )
            ->all();
    }

    protected function groupFor(CcSetting $setting): Group
    {
        $label = str($setting->setting_label)->replace('_', ' ')->title();

        return Group::make([
            $this->fieldFor($setting)
                ->label($label)
                ->helperText($setting->description ?? ''),
        ]);
    }

    protected function fieldFor(CcSetting $setting): \Filament\Schemas\Components\Component
    {
        $options = $this->optionsFor($setting);

        $field = match ($setting->value_presentation) {
            'boolean' => Toggle::make($setting->setting_code)->inline(false),

            'select' => Select::make($setting->setting_code)
                ->options($options)
                ->native(false)
                ->reactive()
                ->dehydrateStateUsing(fn($state) => $state)
                ->afterStateHydrated(function ($component, $state) use ($options) {
                    if ($state && !array_key_exists($state, $options)) {
                        $reverse = array_flip($options);
                        if (isset($reverse[$state])) {
                            $component->state($reverse[$state]);
                        }
                    }
                }),

            'multi-select' => Select::make($setting->setting_code)
                ->multiple()
                ->options($options)
                ->native(false)
                ->reactive()
                ->dehydrateStateUsing(fn($state) => $state),

            'date' => DatePicker::make($setting->setting_code),
            'text' => Textarea::make($setting->setting_code)->rows(1),
            default => TextInput::make($setting->setting_code),
        };

        // --- Emoji color preview for color_scheme ---
        if ($setting->setting_code === 'color_scheme' && $field instanceof Select) {
            $colorKeys = [
                'sky', 'blue', 'indigo', 'purple', 'pink', 'rose',
                'red', 'orange', 'amber',
//                'yellow', -- for some reason this doesn't show a color preview
                'lime', 'green',
                'emerald', 'teal', 'cyan', 'slate', 'gray', 'zinc',
            ];

            $swatchOptions = collect($options)
                ->filter(fn($label, $value) => in_array($value, $colorKeys))
                ->mapWithKeys(function ($label, $value) {
                    $html = <<<HTML
<div class="flex items-center gap-2">
    <span class="w-4 h-4 rounded-full bg-{$value}-500 border border-gray-300 shadow-sm"></span>
    <span>{$label}</span>
</div>
HTML;
                    return [$value => $html];
                })
                ->all();

            $field->options($swatchOptions)->allowHtml();
        }

        return $field;

    }

    protected function optionsFor(CcSetting $setting): array
    {
        // Color scheme options
        if ($setting->setting_code === 'color_scheme') {
            return [
                'sky' => 'Sky',
                'blue' => 'Blue',
                'indigo' => 'Indigo',
                'purple' => 'Purple',
                'pink' => 'Pink',
                'rose' => 'Rose',
                'red' => 'Red',
                'orange' => 'Orange',
                'amber' => 'Amber',
//                'yellow' => 'Yellow', -- for some reason this doesn't show a color preview'
                'lime' => 'Lime',
                'green' => 'Green',
                'emerald' => 'Emerald',
                'teal' => 'Teal',
                'cyan' => 'Cyan',
                'slate' => 'Slate',
            ];
        }

        // Otherwise, pull from options_json if present
        if (!empty($setting->options_json)) {
            try {
                $decoded = json_decode($setting->options_json, true);
                return collect($decoded)
                    ->mapWithKeys(fn($value, $label) => [$value => $label])
                    ->sort(fn($a, $b) => strcasecmp($a, $b))
                    ->all();
            } catch (\Throwable $e) {
                Log::warning("Invalid JSON in options_json for setting {$setting->setting_code}: {$e->getMessage()}");
            }
        }

        return [];
    }

    /** Filament theme colour options (with colour blobs) */
    protected function colorOptions(): array
    {
        return [
            'Sky' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-sky-500"></span>Sky</div>',
            'Blue' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-blue-500"></span>Blue</div>',
            'Indigo' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-indigo-500"></span>Indigo</div>',
            'Purple' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-purple-500"></span>Purple</div>',
            'Pink' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-pink-500"></span>Pink</div>',
            'Rose' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-rose-500"></span>Rose</div>',
            'Red' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-red-500"></span>Red</div>',
            'Orange' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-orange-500"></span>Orange</div>',
            'Amber' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-amber-500"></span>Amber</div>',
            'Lime' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-lime-500"></span>Lime</div>',
            'Green' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-green-500"></span>Green</div>',
            'Emerald' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-emerald-500"></span>Emerald</div>',
            'Teal' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-teal-500"></span>Teal</div>',
            'Cyan' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-cyan-500"></span>Cyan</div>',
            'Gray' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-gray-500"></span>Gray</div>',
            'Neutral' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-neutral-500"></span>Neutral</div>',
            'Stone' => '<div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-stone-500"></span>Stone</div>',
        ];
    }


    public function save(): void
    {
        $state = $this->content->getState();
        $reloadRequired = false;

        foreach ($state as $code => $value) {
            $setting = CcSetting::query()->where('setting_code', $code)->first();

            if (!$setting) {
                continue;
            }

            $oldValue = $setting->setting_value;
            $newValue = is_array($value) ? json_encode($value) : $value;

            // Only update if changed
            if ($oldValue !== $newValue) {
                $setting->update(['setting_value' => $newValue]);

                // If the updated setting affects the theme, mark for reload
                if (in_array($code, ['color_scheme', 'theme_color', 'primary_color'])) {
                    $reloadRequired = true;
                }
            }
        }

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();

        // ✅ Reload only if theme-related settings were updated
        if ($reloadRequired) {
            $this->js(<<<'JS'
            setTimeout(() => window.location.reload(), 800);
        JS
            );
        }
    }

    protected function syncFromConfig(): void
    {
        $config = config('ccsettings');

        foreach ($config as $groupCode => $groupData) {
            $group = CcSettingGroup::updateOrCreate(
                ['code' => $groupCode],
                [
                    'label' => $groupData['label'] ?? ucfirst($groupCode),
                    'display_seq' => $groupData['order'] ?? 0,
                ]
            );

            foreach (($groupData['settings'] ?? []) as $settingCode => $settingData) {
                CcSetting::updateOrCreate(
                    [
                        'setting_group_id' => $group->id,
                        'setting_code' => $settingCode,
                    ],
                    [
                        'setting_label' => $settingData['label'] ?? ucfirst(str_replace('_', ' ', $settingCode)),
                        'default_value' => $settingData['default'] ?? null,
                        'value_presentation' => $settingData['presentation'] ?? 'text',
                        'description' => $settingData['description'] ?? null,
                        'options_json' => isset($settingData['options'])
                            ? json_encode($settingData['options'])
                            : null,
                        'display_seq' => $settingData['order'] ?? 0,
                    ]
                );
            }
        }
    }
}
