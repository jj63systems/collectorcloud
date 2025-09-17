<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

    protected $casts = [
        'system_flag' => 'boolean',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(CcLookupType::class, 'type_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['label', 'sort_order', 'enabled'])
            ->useLogName('Lookup Values')
            ->logOnlyDirty(); // optional: use a custom log name
    }

    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = strtoupper($value);
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }
}
