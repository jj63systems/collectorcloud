<?php

namespace App\Models\Tenant;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'guard_name',
    ];
}
