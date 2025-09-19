<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CcTeam extends Model
{
    use UsesTenantConnection;

    protected $fillable = ['name'];

    public function overrides()
    {
        return $this->hasMany(CcLabelOverride::class, 'team_id');
    }

    public function users()
    {
        return $this->belongsToMany(
            \App\Models\User::class,
            'cc_team_user',
            'team_id',
            'user_id'
        )->withTimestamps()
            ->withPivot('role');
    }
}
