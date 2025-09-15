<?php

namespace Database\Seeders\tenant;

use App\Models\tenant\CcLocation;
use App\Models\tenant\TenantActivity;
use Illuminate\Database\Seeder;

class TestLocationSeeder extends Seeder
{
    public function run(): void
    {
        // Remove activity logs just for CcLocation
        TenantActivity::whereIn('log_name', ['Locations', 'cc_location'])
            ->delete();

        // Now clear the test data itself
        CcLocation::truncate();

        $total = 0;
        $sites = collect();

        // Level 1: Sites
        for ($i = 1; $i <= 5; $i++) {
            $site = CcLocation::create([
                'name' => "Site $i",
                'type' => 'Site',
                'parent_id' => null,
                'depth' => 1,
            ]);
            $sites->push($site);
            $total++;
        }

        $buildings = collect();
        foreach ($sites as $site) {
            for ($i = 1; $i <= 4; $i++) {
                $building = CcLocation::create([
                    'name' => "Building $i",
                    'type' => 'Building',
                    'parent_id' => $site->id,
                    'depth' => 2,
                ]);
                $buildings->push($building);
                $total++;
            }
        }

        $rooms = collect();
        foreach ($buildings as $building) {
            for ($i = 1; $i <= 5; $i++) {
                $room = CcLocation::create([
                    'name' => "Room $i",
                    'type' => 'Room',
                    'parent_id' => $building->id,
                    'depth' => 3,
                ]);
                $rooms->push($room);
                $total++;
            }
        }

        $units = collect();
        foreach ($rooms as $room) {
            for ($i = 1; $i <= 3; $i++) {
                $unit = CcLocation::create([
                    'name' => "Unit $i",
                    'type' => 'Unit',
                    'parent_id' => $room->id,
                    'depth' => 4,
                ]);
                $units->push($unit);
                $total++;
            }
        }

        foreach ($units as $unit) {
            for ($i = 1; $i <= 2; $i++) {
                CcLocation::create([
                    'name' => "Container $i",
                    'type' => 'Container',
                    'parent_id' => $unit->id,
                    'depth' => 5,
                ]);
                $total++;

                if ($total >= 500) {
                    break 2;
                } // Stop at 500 total
            }
        }

        $this->command->info("Seeded $total locations without parent-type repetition in names.");
    }
}
