<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


class CcLabelOverride extends Model
{

    use UsesTenantConnection;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'tenant_id',
        'resource_id',
        'locale',
        'key',
        'value',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
    ];
}
