<?php

namespace App\Models\Tenant;

use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use UsesTenantConnection;

    protected $fillable = [
        'name',
        'guard_name',
    ];
}
