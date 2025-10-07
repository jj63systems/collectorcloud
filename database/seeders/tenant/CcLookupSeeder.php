<?php

namespace Database\Seeders\tenant;

use App\Models\Tenant\CcLookupType;
use App\Models\Tenant\CcLookupValue;
use Illuminate\Database\Seeder;

class CcLookupSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * ----------------------------------------------------------
         * Define lookup types and their values here.
         *
         * Each element in the $lookups array defines:
         *   - code          (string, unique key for the type)
         *   - name          (friendly label)
         *   - is_team_scoped (bool)
         *   - values         (assoc array of CODE => Label)
         * ----------------------------------------------------------
         */
        $lookups = [

            // ─────────── LOCATION TYPES ───────────
            [
                'code' => 'LOCATION_TYPE',
                'name' => 'Location Type',
                'is_team_scoped' => false,
                'values' => [
                    'SITE' => 'Site (e.g. museum grounds)',
                    'BUILDING' => 'Building (within a site)',
                    'ROOM' => 'Room (within a building)',
                    'CABINET' => 'Cabinet (storage or display)',
                    'CONTAINER' => 'Container (box, folder, etc)',
                ],
            ],

            // ─────────── ITEM CATEGORIES (example) ───────────
            [
                'code' => 'ITEM_TYPE',
                'name' => 'Item Type',
                'is_team_scoped' => true,
                'values' => [
                    'VISUAL_MEDIA' => 'Visual Media (photos, films, etc)',
                    'TEXTILES' => 'Textiles (uniforms, flags, etc)',
                    'DOCUMENTS' => 'Documents (letters, logbooks, etc)',
                    'ARTEFACTS' => 'Artefacts (tools, instruments, etc)',
                ],
            ],

            // ─────────── (add more types below) ───────────
            // [
            //     'code' => 'YOUR_NEW_TYPE',
            //     'name' => 'Descriptive Name',
            //     'is_team_scoped' => true/false,
            //     'values' => [
            //         'VALUE_CODE' => 'Label for this value',
            //         'ANOTHER' => 'Another label',
            //     ],
            // ],
        ];

        /**
         * ----------------------------------------------------------
         * Seed all lookup types & values
         * ----------------------------------------------------------
         */
        foreach ($lookups as $lookupData) {

            $type = CcLookupType::updateOrCreate(
                ['code' => $lookupData['code']],
                [
                    'name' => $lookupData['name'],
                    'is_team_scoped' => $lookupData['is_team_scoped'] ?? false,
                ]
            );

            $sort = 1;

            foreach ($lookupData['values'] as $code => $label) {
                CcLookupValue::updateOrCreate(
                    [
                        'type_id' => $type->id,
                        'code' => $code,
                    ],
                    [
                        'label' => $label,
                        'sort_order' => $sort++,
                        'system_flag' => false,
                        'enabled' => true,
                    ]
                );
            }
        }
    }
}
