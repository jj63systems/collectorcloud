<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use UsesTenantConnection;

    protected $fillable = [
        'name',
        'description',
        'guard_name',
        'team_id',
    ];

    public function ccTeams(): BelongsToMany
    {
        return $this->belongsToMany(
            CcTeam::class,
            'team_allowed_roles',
            'role_id',
            'team_id'
        );
    }
}
