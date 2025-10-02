<?php

namespace App\Models\Traits;

use App\Models\Tenant\CcTeam;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;

trait HasTeamRoles
{
    use HasRoles {
        roles as protected baseRoles;
        assignRole as protected baseAssignRole;
        hasRole as protected baseHasRole;
        syncRoles as protected baseSyncRoles;
        getRoleNames as protected baseGetRoleNames;
        hasPermissionTo as protected baseHasPermissionTo;
    }

    /** Simple per-request caches */
    protected array $roleCache = [];
    protected array $permissionCache = [];

    /**
     * Roles relationship (global, not team-scoped).
     */
    public function roles(): BelongsToMany
    {
        return $this->baseRoles();
    }

    /**
     * Assign one or more roles globally to the user.
     */
    public function assignRole($roles): self
    {
        $this->baseAssignRole($roles);

        $this->roleCache = [];
        $this->permissionCache = [];

        return $this;
    }

    /**
     * Replace all roles globally for the user.
     */
    public function syncRoles($roles): self
    {
        $this->baseSyncRoles($roles);

        $this->roleCache = [];
        $this->permissionCache = [];

        return $this;
    }

    /**
     * Get the names of all roles the user has *effective* in the given team.
     */
    public function getRoleNames(?CcTeam $team = null)
    {
        $team = $this->resolveTeam($team);
        $cacheKey = 'roles:'.$team->id;

        if (isset($this->roleCache[$cacheKey])) {
            return $this->roleCache[$cacheKey];
        }

        $roleNames = $this->baseRoles()
            ->whereIn('roles.id', function ($q) use ($team) {
                $q->select('role_id')
                    ->from('team_allowed_roles')
                    ->where('team_id', $team->id);
            })
            ->pluck('roles.name');

        return $this->roleCache[$cacheKey] = $roleNames;
    }

    /**
     * Check if the user has the given role(s) *effective* in the given team.
     */
    public function hasRole($roles, ?CcTeam $team = null): bool
    {
        $team = $this->resolveTeam($team);
        $roles = (array) $roles;
        $cacheKey = 'hasRole:'.$team->id.':'.implode(',', $roles);

        if (isset($this->roleCache[$cacheKey])) {
            return $this->roleCache[$cacheKey];
        }

        $result = $this->baseRoles()
            ->whereIn('roles.name', $roles)
            ->whereIn('roles.id', function ($q) use ($team) {
                $q->select('role_id')
                    ->from('team_allowed_roles')
                    ->where('team_id', $team->id);
            })
            ->exists();

        return $this->roleCache[$cacheKey] = $result;
    }

    /**
     * Check if the user has the given permission *effective* in the given team.
     */
    public function hasPermissionTo($permission, $team = null, $guardName = null): bool
    {
        // Case: called by Spatie with ($permission, $guardName)
        if (is_string($team) && $guardName === null) {
            $guardName = $team;
            $team = null;
        }

        // Default to current team if none provided
        $team = $team ?: $this->currentTeam;

        if (!$team instanceof CcTeam) {
            // Fallback: use base Spatie logic (global check)
            return $this->baseHasPermissionTo($permission, $guardName);
        }

        $cacheKey = 'perm:'.$team->id.':'.$permission.':'.($guardName ?? 'web');

        if (isset($this->permissionCache[$cacheKey])) {
            return $this->permissionCache[$cacheKey];
        }

        $query = $this->baseRoles()
            ->whereIn('roles.id', function ($q) use ($team) {
                $q->select('role_id')
                    ->from('team_allowed_roles')
                    ->where('team_id', $team->id);
            })
            ->whereHas('permissions', function ($q) use ($permission, $guardName) {
                $q->where('name', $permission);

                if ($guardName) {
                    $q->where('guard_name', $guardName);
                }
            });

        return $this->permissionCache[$cacheKey] = $query->exists();
    }

    /**
     * Helper: always resolve to a CcTeam instance.
     */
    protected function resolveTeam($team): CcTeam
    {
        if ($team instanceof CcTeam) {
            return $team;
        }

        if ($team === null && $this->currentTeam instanceof CcTeam) {
            return $this->currentTeam;
        }

        throw new \InvalidArgumentException('Expected instance of CcTeam, got '.gettype($team));
    }
}
