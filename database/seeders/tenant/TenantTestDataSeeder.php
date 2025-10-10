<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\CcTeam;
use App\Models\Tenant\TenantUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TenantTestDataSeeder extends Seeder
{
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
            ['email' => $dbName.'@example.com'],
            [
                'name' => $dbName,
                'password' => Hash::make('test1234'),
                'is_superuser' => true,
            ]
        );

        if ($user->wasRecentlyCreated) {
            \Log::info("Superuser created: {$user->email}");
        } else {
            \Log::info("Superuser already existed: {$user->email}");
        }

        // Link superuser only to the Default Team and set current_team_id if missing
        $user->teams()->syncWithoutDetaching([$defaultTeam->id]);

        if (!$user->current_team_id) {
            $user->update(['current_team_id' => $defaultTeam->id]);
        }

        // ✅ Assign the "superuser" role if available
        $superuserRole = Role::where('name', 'superuser')
            ->where('guard_name', 'tenant')
            ->first();

        if ($superuserRole) {
            $user->syncRoles([$superuserRole->name]);
            \Log::info("Superuser role assigned to {$user->email}");
        } else {
            \Log::warning("Superuser role not found when seeding {$user->email}");
        }


        $this->call(TestItemSeeder::class);
        $this->call(TestLookupSeeder::class);

    }

}
