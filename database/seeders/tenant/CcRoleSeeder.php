<?php

namespace Database\Seeders\tenant;

use App\Models\Tenant\CcTeam;
use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use App\Models\Tenant\TenantUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CcRoleSeeder extends Seeder
{
    public function run(): void
    {
        $team = CcTeam::firstOrCreate(['name' => 'Default Team']);

        // Default permissions (expand later)
        $defaultPermissions = [
            'cc_locations.view',
            'cc_locations.create',
            'cc_locations.edit',
            'cc_locations.delete',
        ];

        foreach ($defaultPermissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'tenant',
            ]);
        }

        // Default roles and their permissions
        $roles = [
            'superuser' => $defaultPermissions,
            'archivist' => [
                'cc_locations.view',
                'cc_locations.create',
                'cc_locations.edit',
            ],
            'researcher' => [
                'cc_locations.view',
            ],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'tenant',
                'team_id' => $team->id,
            ]);

            $role->syncPermissions($perms);
        }

        // Ensure at least one superuser exists
        $superUser = TenantUser::where('is_superuser', true)->first();

        if (!$superUser) {
            $superUser = TenantUser::create([
                'name' => 'Super User',
                'email' => 'superuser@example.com',
                'password' => Hash::make('password'), // change in production
                'is_superuser' => true,
                'current_team_id' => $team->id, // ✅ set default team
            ]);
        }

        // Ensure superuser is linked to team
        $superUser->teams()->syncWithoutDetaching([$team->id]);

        // Ensure current_team_id is set (in case the user already existed)
        if (!$superUser->current_team_id) {
            $superUser->update(['current_team_id' => $team->id]);
        }

        // Ensure superuser has the "superuser" role for THIS team
        if (!$superUser->hasRole('superuser', $team)) {
            $superUser->assignRole('superuser', $team);
        }

        // ✅ Ensure ALL users in this tenant have a current_team_id set
        Log::info("Setting current_team_id for all users in team: $team->id");
        TenantUser::whereNull('current_team_id')->update([
            'current_team_id' => $team->id,
        ]);
    }
}
