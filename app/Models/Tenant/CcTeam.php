<?php

namespace App\Models\Tenant;

use Database\Seeders\tenant\CcFieldMappingsSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CcTeam extends Model
{
    use UsesTenantConnection;

    protected $fillable = ['name'];


    protected static function booted(): void
    {
        static::created(function (CcTeam $team) {
            CcFieldMappingsSeeder::seedForTeam($team->id);
        });
    }

    /**
     * Users belonging to this team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(TenantUser::class, 'cc_team_user', 'team_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Roles allowed for this team (via pivot table team_allowed_roles).
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'team_allowed_roles', 'team_id', 'role_id')
            ->withTimestamps();
    }


}
