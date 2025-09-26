<?php

namespace App\Services;

use App\Models\Tenant\CcTeam;
use App\Models\Tenant\TenantUser;

class AccessService
{
    public static function canAccess(TenantUser $user, CcTeam $team, string $permission): bool
    {
        if ($user->is_superuser) {
            return true;
        }

        return $user->roles()
            ->wherePivot('team_id', $team->id)
            ->whereHas('permissions', fn($q) => $q->where('name', $permission))
            ->exists();
    }
}
