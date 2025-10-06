<?php

namespace App\Services;

use App\Models\Tenant\CcTeam;
use App\Models\Tenant\TenantUser;

class PermissionContext
{
    // Cache key: user_id:team_id:guard
    protected static array $cache = [];

    public static function getEffectivePermissions(TenantUser $user, CcTeam $team, string $guardName = 'web'): array
    {
        $key = $user->id.':'.$team->id.':'.$guardName;

        if (!isset(self::$cache[$key])) {
            $permissions = $user->roles()
                ->whereIn('roles.id', function ($q) use ($team) {
                    $q->select('role_id')
                        ->from('team_allowed_roles')
                        ->where('team_id', $team->id);
                })
                ->with('permissions') // eager load
                ->get()
                ->flatMap(fn($role) => $role->permissions)
                ->filter(fn($permission) => $permission->guard_name === $guardName)
                ->pluck('name')
                ->unique()
                ->values()
                ->all();

            self::$cache[$key] = $permissions;
        }

        return self::$cache[$key];
    }

    public static function userHas(TenantUser $user, CcTeam $team, string $permission, string $guardName = 'web'): bool
    {
        return in_array($permission, self::getEffectivePermissions($user, $team, $guardName), true);
    }
}
