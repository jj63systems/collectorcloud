<?php

namespace Database\Seeders\tenant;

use App\Models\Tenant\TenantUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

        // Determine db name from the current connection
        $dbName = DB::connection('tenant')->getDatabaseName();

        // Create super user if it doesn't already exist
        TenantUser::firstOrCreate(
            ['email' => $dbName.'@example.com'], // lookup
            [
                'name' => 'Super User',
                'password' => Hash::make('test1234'), // change as needed
                'is_superuser' => true, // assumes you have a flag/role column
            ]
        );


    }
}
