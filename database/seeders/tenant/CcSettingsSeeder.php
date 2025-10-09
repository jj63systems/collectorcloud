<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\CcSetting;
use App\Models\Tenant\CcSettingGroup;
use Illuminate\Database\Seeder;

class CcSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $config = config('ccsettings');

        foreach ($config as $groupCode => $groupData) {
            // Upsert the setting group
            $group = CcSettingGroup::updateOrCreate(
                ['code' => $groupCode],
                [
                    'label' => $groupData['label'] ?? ucfirst($groupCode),
                    'display_seq' => $groupData['order'] ?? 0,
                ]
            );

            // Iterate through each setting in the group
            foreach ($groupData['settings'] as $settingCode => $setting) {
                CcSetting::updateOrCreate(
                    [
                        'setting_group_id' => $group->id,
                        'setting_code' => $settingCode,
                    ],
                    [
                        'setting_label' => $setting['label'] ?? ucfirst($settingCode),
                        'setting_value' => $setting['default'] ?? null,
                        'default_value' => $setting['default'] ?? null,
                        'is_locked' => $setting['is_locked'] ?? false,
                        'unlock_message' => $setting['unlock_message'] ?? null,
                        'value_presentation' => $setting['presentation'] ?? 'text',
                        'description' => $setting['description'] ?? null,
                        'options_json' => isset($setting['options'])
                            ? json_encode($setting['options'])
                            : null,
                        'display_seq' => $setting['order'] ?? 0,
                    ]
                );
            }
        }
    }
}
