<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;


// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        activity()->disableLogging();

        // Call the lookup seeder
        $this->call(CcLookupSeeder::class);

    }
}
