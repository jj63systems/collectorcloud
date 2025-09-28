<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
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
