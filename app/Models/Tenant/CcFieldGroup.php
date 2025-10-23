<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CcFieldGroup extends Model
{
    use UsesTenantConnection;

    protected $table = 'cc_field_groups';

    protected $fillable = [
        'name',
        'display_seq',
        'is_protected',
        'team_id'
    ];

    public function fieldMappings(): HasMany
    {
        return $this->hasMany(CcFieldMapping::class, 'field_group_id');
    }
}
