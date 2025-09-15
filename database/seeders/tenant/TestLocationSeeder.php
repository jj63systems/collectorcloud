<?php

namespace Database\Seeders\tenant;

use App\Models\tenant\CcLocation;
use App\Models\tenant\CcLookupValue;
use App\Models\tenant\TenantActivity;
use Illuminate\Database\Seeder;

class TestLocationSeeder extends Seeder
{
    public function run(): void
    {
        // Remove activity logs just for CcLocation
        TenantActivity::whereIn('log_name', ['Locations', 'cc_location'])->delete();

        // Clear the test locations
        CcLocation::truncate();

        // Ensure all required lookup codes exist
        $requiredCodes = ['SITE', 'BUILDING', 'ROOM', 'CABINET', 'CONTAINER'];
        $types = [];

        foreach ($requiredCodes as $code) {
            $id = CcLookupValue::where('code', $code)->value('id');
            if (!$id) {
                throw new \Exception("Missing required lookup value for code: $code");
            }
            $types[$code] = $id;
        }

        $total = 0;
        $sites = collect();

        // Level 1: Sites
        for ($i = 1; $i <= 5; $i++) {
            $site = CcLocation::create([
                'name' => "Site $i",
                'type_id' => $types['SITE'],
                'parent_id' => null,
            ]);
            $sites->push($site);
            $total++;
        }

        $buildings = collect();
        foreach ($sites as $site) {
            for ($i = 1; $i <= 4; $i++) {
                $building = CcLocation::create([
                    'name' => "Building $i",
                    'type_id' => $types['BUILDING'],
                    'parent_id' => $site->id,
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
                    'type_id' => $types['ROOM'],
                    'parent_id' => $building->id,
                ]);
                $rooms->push($room);
                $total++;
            }
        }

        $cabinets = collect();
        foreach ($rooms as $room) {
            for ($i = 1; $i <= 3; $i++) {
                $cabinet = CcLocation::create([
                    'name' => "Cabinet $i",
                    'type_id' => $types['CABINET'],
                    'parent_id' => $room->id,
                ]);
                $cabinets->push($cabinet);
                $total++;
            }
        }

        foreach ($cabinets as $cabinet) {
            for ($i = 1; $i <= 2; $i++) {
                CcLocation::create([
                    'name' => "Container $i",
                    'type_id' => $types['CONTAINER'],
                    'parent_id' => $cabinet->id,
                ]);
                $total++;

                if ($total >= 500) {
                    break 2; // Exit both loops when total hits 500
                }
            }
        }

        CcLocation::updateAllPathsAndDepths();

        $this->command->info("Seeded $total locations using type_id from lookup values.");
    }
}
