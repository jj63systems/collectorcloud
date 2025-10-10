<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CcLookupValue extends Model
{
    use UsesTenantConnection, LogsActivity;

    protected $table = 'cc_lookup_values';

    protected $fillable = [
        'type_id',
        'code',
        'label',
        'sort_order',
        'system_flag',
        'enabled',
        'color',
    ];

    protected $casts = [
        'system_flag' => 'boolean',
        'enabled' => 'boolean',
    ];

    /**
     * The lookup type this value belongs to.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(CcLookupType::class, 'type_id');
    }

    /**
     * Teams that this lookup value is available to (team-scoped types only).
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(
            CcTeam::class,
            'cc_lookup_value_team',
            'lookup_value_id',
            'team_id'
        )->withTimestamps()
            ->withPivot(['is_default', 'meta']);
    }

    /**
     * Scope: only enabled values.
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Automatically uppercase the code.
     */
    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = strtoupper($value);
    }

    /**
     * Activity logging setup.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['label', 'sort_order', 'enabled'])
            ->useLogName('Lookup Values')
            ->logOnlyDirty();
    }
}
