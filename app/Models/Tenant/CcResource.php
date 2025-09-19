<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CcResource extends Model
{
    use UsesTenantConnection;

    protected $fillable = ['code', 'name'];

    public function overrides()
    {
        return $this->hasMany(CcLabelOverride::class, 'resource_id');
    }
}
