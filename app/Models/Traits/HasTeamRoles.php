<?php

namespace App\Models\Traits;

use App\Models\Tenant\CcTeam;
use App\Models\Tenant\Role;
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
     * Roles relationship, always scoped to the current team by default.
     */
    public function roles(): BelongsToMany
    {
        return $this->baseRoles()->wherePivot('team_id', $this->current_team_id);
    }

    /**
     * Assign one or more roles to the user for a given team.
     */
    public function assignRole($roles, CcTeam $team = null): self
    {
        $team = $team ?: $this->currentTeam;

        $roleIds = collect($roles)->map(function ($role) use ($team) {
            if ($role instanceof Role) {
                return $role->id;
            }

            return Role::where('name', $role)
                ->where('team_id', $team->id)
                ->firstOrFail()
                ->id;
        })->toArray();

        foreach ($roleIds as $roleId) {
            $this->baseRoles()->syncWithoutDetaching([
                $roleId => ['team_id' => $team->id],
            ]);
        }

        // Clear cache so new roles are visible immediately
        $this->roleCache = [];
        $this->permissionCache = [];

        return $this;
    }

    /**
     * Remove one or more roles from the user for a given team.
     */
    public function removeRole($roles, CcTeam $team = null): self
    {
        $team = $team ?: $this->currentTeam;

        $roleIds = collect($roles)->map(function ($role) use ($team) {
            if ($role instanceof Role) {
                return $role->id;
            }

            return Role::where('name', $role)
                ->where('team_id', $team->id)
                ->firstOrFail()
                ->id;
        })->toArray();

        $this->baseRoles()
            ->newPivotStatementForId($roleIds, $this)
            ->where('team_id', $team->id)
            ->delete();

        $this->roleCache = [];
        $this->permissionCache = [];

        return $this;
    }

    /**
     * Replace all roles for the user in a given team.
     */
    public function syncRoles($roles, CcTeam $team = null): self
    {
        $team = $team ?: $this->currentTeam;

        $roleIds = collect($roles)->map(function ($role) use ($team) {
            if ($role instanceof Role) {
                return $role->id;
            }

            return Role::where('name', $role)
                ->where('team_id', $team->id)
                ->firstOrFail()
                ->id;
        })->toArray();

        $this->baseRoles()->wherePivot('team_id', $team->id)->detach();

        foreach ($roleIds as $roleId) {
            $this->baseRoles()->attach($roleId, ['team_id' => $team->id]);
        }

        $this->roleCache = [];
        $this->permissionCache = [];

        return $this;
    }

    /**
     * Get the names of all roles the user has for the given team.
     */
    public function getRoleNames(CcTeam $team = null)
    {
        $team = $team ?: $this->currentTeam;
        $cacheKey = $team->id;

        if (isset($this->roleCache[$cacheKey])) {
            return $this->roleCache[$cacheKey];
        }

        return $this->roleCache[$cacheKey] = $this->baseRoles()
            ->where('model_has_roles.team_id', $team->id)
            ->pluck('roles.name');
    }

    /**
     * Check if the user has the given role(s) in the given team.
     */
    public function hasRole($roles, CcTeam $team = null): bool
    {
        $team = $team ?: $this->currentTeam;
        $roles = (array) $roles;
        $cacheKey = $team->id.'|'.implode(',', $roles);

        if (isset($this->roleCache[$cacheKey])) {
            return $this->roleCache[$cacheKey];
        }

        return $this->roleCache[$cacheKey] = $this->baseRoles()
            ->where('model_has_roles.team_id', $team->id)
            ->whereIn('roles.name', $roles)
            ->exists();
    }

    /**
     * Check if the user has the given permission in the given team.
     */
    public function hasPermissionTo($permission, $team = null, $guardName = null): bool
    {
        $team = $team ?: $this->currentTeam;
        $cacheKey = $team->id.'|'.$permission.'|'.($guardName ?? 'web');

        if (isset($this->permissionCache[$cacheKey])) {
            return $this->permissionCache[$cacheKey];
        }

        $result = $this->roles()
            ->where('model_has_roles.team_id', $team->id)
            ->whereHas('permissions', function ($q) use ($permission, $guardName) {
                $q->where('name', $permission);

                if ($guardName) {
                    $q->where('guard_name', $guardName);
                }
            })
            ->exists();

        return $this->permissionCache[$cacheKey] = $result;
    }
}
