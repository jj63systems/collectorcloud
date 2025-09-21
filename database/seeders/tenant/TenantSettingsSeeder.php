<?php

namespace Database\Seeders\tenant;

use App\Models\Tenant\CcSetting;
use App\Models\Tenant\CcSettingGroup;
use Illuminate\Database\Seeder;

class TenantSettingsSeeder extends Seeder
{
    /**
     * Seed default settings for a tenant.
     */
    public function run(): void
    {
        // Example: General settings group
        $generalGroup = CcSettingGroup::firstOrCreate(
            ['name' => 'General'],
            ['display_seq' => 1]
        );

        // Example settings
        $settings = [

            ['setting_code' => 'language', 'setting_value' => 'en'],
        ];

        foreach ($settings as $setting) {
            CcSetting::firstOrCreate(
                [
                    'setting_code' => $setting['setting_code'],
                    'setting_group_id' => $generalGroup->id,
                ],
                ['setting_value' => $setting['setting_value']]
            );
        }

        // Add more groups + settings here as your defaults grow
    }
}
