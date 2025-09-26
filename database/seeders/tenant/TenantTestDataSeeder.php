<?php

namespace Database\Seeders\tenant;

use App\Models\Tenant\CcTeam;
use App\Models\Tenant\TenantUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantTestDataSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        activity()->disableLogging();

        $this->call(TestLocationSeeder::class);

        // Ensure three teams exist
        $defaultTeam = CcTeam::firstOrCreate(['name' => 'Default Team']);
        CcTeam::firstOrCreate(['name' => 'TEAM2']);
        CcTeam::firstOrCreate(['name' => 'TEAM3']);

        // Create 10 dummy users and attach them only to the Default Team
        TenantUser::factory(10)->create()->each(function (TenantUser $user) use ($defaultTeam) {
            $user->teams()->syncWithoutDetaching([$defaultTeam->id]);

            if (!$user->current_team_id) {
                $user->update(['current_team_id' => $defaultTeam->id]);
            }
        });

        // Determine db name from the current connection
        $dbName = DB::connection('tenant')->getDatabaseName();

        // Create super user if it doesn't already exist
        $user = TenantUser::firstOrCreate(
            ['email' => $dbName.'@example.com'], // lookup
            [
                'name' => $dbName,
                'password' => Hash::make('test1234'), // change as needed
                'is_superuser' => true,
            ]
        );

        // Link superuser only to the Default Team and set current_team_id if missing
        $user->teams()->syncWithoutDetaching([$defaultTeam->id]);

        if (!$user->current_team_id) {
            $user->update(['current_team_id' => $defaultTeam->id]);
        }
    }
}
