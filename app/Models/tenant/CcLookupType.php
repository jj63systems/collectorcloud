<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CcLookupType extends Model
{
    use UsesTenantConnection;

    protected $table = 'cc_lookup_types';

    protected $fillable = [
        'code',
        'name',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(CcLookupValue::class, 'type_id');
    }
}
