<?php

namespace Database\Seeders\tenant;

use App\Models\Tenant\TenantUser;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TenantTestDataSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        activity()->disableLogging();

        $this->call(TestLocationSeeder::class);

        // This is the correct way
        TenantUser::factory(10)->create();
    }
}
