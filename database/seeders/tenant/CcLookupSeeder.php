<?php

namespace Database\Seeders\tenant;

use App\Models\Tenant\CcLookupType;
use App\Models\Tenant\CcLookupValue;
use Illuminate\Database\Seeder;

class CcLookupSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update the lookup type
        $type = CcLookupType::updateOrCreate(
            ['code' => 'LOCATION_TYPE'],
            ['name' => 'Location Type']
        );

        // Define the values
        $values = [
            'SITE' => 'Site (e.g. museum grounds)',
            'BUILDING' => 'Building (within a site)',
            'ROOM' => 'Room (within a building)',
            'CABINET' => 'Cabinet (storage or display)',
            'CONTAINER' => 'Container (box, folder, etc)',
        ];

        $index = 1;

        foreach ($values as $code => $label) {
            CcLookupValue::updateOrCreate(
                [
                    'type_id' => $type->id,
                    'code' => $code,
                ],
                [
                    'label' => $label,
                    'sort_order' => $index++,
                    'system_flag' => false,
                ]
            );
        }
    }
}
