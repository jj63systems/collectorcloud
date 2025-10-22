<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


class CcDataLoad extends Model
{

    use UsesTenantConnection;

    protected $table = 'cc_data_loads';


    protected $fillable = [
        'team_id',
        'user_id',
        'filename',
        'worksheet_name',
        'uploaded_at',
        'status',
        'row_count',
        'rows_processed',
        'notes',
        'sample_rows',
        'confirmed_field_mappings',
        'validation_status',
        'validation_progress',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(CcTeam::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class);
    }

    public function stagedItems()
    {
        return $this->hasMany(CcItemStage::class, 'data_load_id');
    }
}
