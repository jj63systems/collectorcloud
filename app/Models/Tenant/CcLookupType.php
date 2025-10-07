<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CcLookupType extends Model
{
    use UsesTenantConnection;

    protected $table = 'cc_lookup_types';

    protected $fillable = [
        'code',
        'name',
        'parent_lookup_type_id',
        'is_team_scoped',
    ];

    protected $casts = [
        'is_team_scoped' => 'boolean',
    ];

    /**
     * All lookup values belonging to this type.
     */
    public function values(): HasMany
    {
        return $this->hasMany(CcLookupValue::class, 'type_id');
    }

    /**
     * Parent lookup type (for hierarchical grouping).
     */
    public function parentType(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_lookup_type_id');
    }

    /**
     * Child lookup types (for hierarchical grouping).
     */
    public function childTypes(): HasMany
    {
        return $this->hasMany(self::class, 'parent_lookup_type_id');
    }
}
