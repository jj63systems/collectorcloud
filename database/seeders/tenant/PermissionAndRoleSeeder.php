<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionAndRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $resources = config('app_permissions.resources');
            $rolesConfig = config('app_permissions.roles');

            // 1. Create all permissions
            foreach ($resources as $resource => $actions) {
                foreach ($actions as $action) {
                    $name = "{$resource}.{$action}";

                    Permission::firstOrCreate(
                        ['name' => $name, 'guard_name' => 'tenant']
                    );
                }
            }

            // 2. Create roles and assign permissions
            foreach ($rolesConfig as $roleName => $permissionNames) {
                $role = Role::firstOrCreate(
                    ['name' => $roleName, 'guard_name' => 'tenant']
                );

                $permissions = Permission::whereIn('name', $permissionNames)
                    ->where('guard_name', 'tenant')
                    ->get();

                $role->syncPermissions($permissions);
            }
        });
    }
}
