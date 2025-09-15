<?php

namespace Database\Seeders\tenant;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'c@example.com',
            'password' => Hash::make('test1234'),

        ]);

        activity()->disableLogging();

        // Call the lookup seeder
        $this->call(CcLookupSeeder::class);

    }
}
