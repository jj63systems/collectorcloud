<?php

namespace Database\Seeders\tenant;

use App\Models\Tenant\CcResource;
use Illuminate\Database\Seeder;

class CcResourceSeeder extends Seeder
{
    /**
     * Seed the cc_resources table.
     */
    public function run(): void
    {
        $resources = [
            ['code' => 'cc_locations', 'name' => 'Locations'],
            ['code' => 'cc_lookups', 'name' => 'Lookups'],
            ['code' => 'cc_donors', 'name' => 'Donors'],
            ['code' => 'cc_items', 'name' => 'Items'],
            // add other resources you want label overrides for
        ];

        foreach ($resources as $resource) {
            CcResource::firstOrCreate(
                ['code' => $resource['code']],
                ['name' => $resource['name']]
            );
        }
    }
}
