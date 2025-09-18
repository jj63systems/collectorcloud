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
        'locale',
        'key',
        'value',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
    ];
}
