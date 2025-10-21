<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CcItemStage extends Model
{
    use UsesTenantConnection;

    protected $table = 'cc_items_stage';

    // ------------------------------------------------------------------
    // Fillable fields — all standard cc_items columns plus fxxx block
    // ------------------------------------------------------------------
    protected $fillable = [
        'team_id',
        'data_load_id',
        'name',
        'item_key',
        'donation_id',
        'location_id',
        'date_received',
        'accessioned_at',
        'accessioned_by',
        'checked_by_user_id',
        'description',
        'filing_reference',
        'condition_notes',
        'curation_notes',
        'disposed',
        'disposed_date',
        'disposed_notes',
        'inventory_status',
        'is_public',
    ];

    // Append all f001–f999 columns dynamically
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Dynamically extend $fillable to include all fxxx fields
        foreach (range(1, 999) as $i) {
            $this->fillable[] = sprintf('f%03d', $i);
        }
    }

    // ------------------------------------------------------------------
    // Casting & defaults
    // ------------------------------------------------------------------
    protected $casts = [
//
    ];

    // ------------------------------------------------------------------
    // Relationships (optional, for later)
    // ------------------------------------------------------------------
    public function team()
    {
        return $this->belongsTo(CcTeam::class);
    }

    public function donation()
    {
        return $this->belongsTo(CcDonation::class);
    }

    public function location()
    {
        return $this->belongsTo(CcLocation::class);
    }

    public function accessionedBy()
    {
        return $this->belongsTo(User::class, 'accessioned_by');
    }

    public function checkedBy()
    {
        return $this->belongsTo(User::class, 'checked_by_user_id');
    }
}
