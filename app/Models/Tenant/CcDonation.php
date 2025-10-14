<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CcDonation extends Model
{

    use UsesTenantConnection;

    protected $table = 'cc_donations';

    protected $fillable = [
        'donor_id',
        'donation_name',
        'date_received',
        'donation_basis',
        'comments',
        'accessioned_by',
        'donation_basis_old',
        'accessioned_by_old',
        'donor_key_old',
        'year_received_old',
    ];

    public function donor(): BelongsTo
    {
        return $this->belongsTo(CcDonor::class, 'donor_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accessioned_by');
    }
}
