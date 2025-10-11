<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CcFieldMapping extends Model
{
    use UsesTenantConnection;

    protected $table = 'cc_field_mappings';

    protected $fillable = [
        'team_id',
        'field_group_id',
        'field_name',
        'label',
        'data_type',
        'max_length',
        'lookup_type_id',
        'display_seq',
        'is_required',
        'is_searchable',
        'is_filterable',
        'is_sortable',
        'toggle_option',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_searchable' => 'boolean',
        'is_filterable' => 'boolean',
        'is_sortable' => 'boolean',
        'toggle_option' => 'string',
    ];

    protected static array $validToggleOptions = [
        'notoggle',
        'toggle_shown',
        'toggle_not_shown',
    ];

    // ────────────────────────────────────────────────
    // RELATIONSHIPS
    // ────────────────────────────────────────────────

    public function team(): BelongsTo
    {
        return $this->belongsTo(CcTeam::class);
    }

    public function lookupType(): BelongsTo
    {
        return $this->belongsTo(CcLookupType::class, 'lookup_type_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(CcLookupType::class, 'lookup_type_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CcFieldGroup::class, 'field_group_id');
    }

    // ────────────────────────────────────────────────
    // SCOPES
    // ────────────────────────────────────────────────

    public static function forTeam(int $teamId)
    {
        return self::query()
            ->where('team_id', $teamId)
            ->whereNotNull('label')
            ->orderBy('display_seq')
            ->get();
    }
}
