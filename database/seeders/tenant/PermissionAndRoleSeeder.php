<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\TenantUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionAndRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $resources = config('app_permissions.resources');

            // 1. Create all permissions
            foreach ($resources as $resource => $actions) {
                foreach ($actions as $action) {
                    $name = "{$resource}.{$action}";

                    Permission::firstOrCreate([
                        'name' => $name,
                        'guard_name' => 'tenant',
                    ]);
                }
            }

            // 2. Define roles with permissions + descriptions
            $roles = [
                'readonly' => [
                    'permissions' => [
                        'cc_locations.view',
                        'cc_teams.view',
                        'cc_users.view',
                    ],
                    'description' => 'Read-only access across the system.',
                ],
                'archivist' => [
                    'permissions' => [
                        'cc_locations.view',
                        'cc_locations.create',
                        'cc_locations.update',
                        'cc_teams.view',
                    ],
                    'description' => 'Manage collections and related data but limited admin rights.',
                ],
                'archive_manager' => [
                    'permissions' => [
                        'cc_locations.view',
                        'cc_locations.create',
                        'cc_locations.update',
                        'cc_locations.delete',
                        'cc_teams.view',
                        'cc_teams.create',
                        'cc_teams.update',
                        'cc_teams.delete',
                        'cc_users.view',
                        'cc_users.update',
                    ],
                    'description' => 'Manage collections and teams, with some user management privileges.',
                ],
                'superuser' => [
                    'permissions' => Permission::pluck('name')->toArray(), // all perms
                    'description' => 'Full access to all features and settings.',
                ],
            ];

            // 3. Create/update roles
            foreach ($roles as $roleName => $data) {
                $role = Role::updateOrCreate(
                    ['name' => $roleName, 'guard_name' => 'tenant'],
                    ['description' => $data['description']]
                );

                if ($role->description !== $data['description']) {
                    $role->description = $data['description'];
                    $role->save();
                }

                $permissions = Permission::whereIn('name', $data['permissions'])
                    ->where('guard_name', 'tenant')
                    ->get();

                $role->syncPermissions($permissions);

                Log::info("Seeded role: {$role->name} ({$role->description})");
            }

            // 4. Assign superuser role to flagged users
            $superuserRole = Role::where('name', 'superuser')
                ->where('guard_name', 'tenant')
                ->first();

            if ($superuserRole) {
                $users = TenantUser::where('is_superuser', true)->get();

                foreach ($users as $user) {
                    $user->syncRoles([$superuserRole->name]);
                }
            }
        });
    }
}
