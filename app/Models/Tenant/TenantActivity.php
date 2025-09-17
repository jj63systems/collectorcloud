<?php

namespace App\Models\Tenant;

use Spatie\Activitylog\Models\Activity as SpatieActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


class TenantActivity extends SpatieActivity
{
    use UsesTenantConnection;
}
