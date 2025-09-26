<?php

namespace Database\Seeders\tenant;

use App\Models\Tenant\CcTeam;
use Illuminate\Database\Seeder;


// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        // Create default team
        $team = CcTeam::create([
            'name' => 'Default Team',
        ]);

        activity()->disableLogging();

        // Call the lookup seeder
        $this->call(CcLookupSeeder::class);

        $this->call(CcResourceSeeder::class);

        $this->call(TenantSettingsSeeder::class);


        // Call new roles/permissions seeder
        $this->call(CcRoleSeeder::class);

    }
}
