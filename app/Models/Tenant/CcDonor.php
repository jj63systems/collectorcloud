<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CcDonor extends Model
{

    use UsesTenantConnection;

    protected $table = 'cc_donors';

    protected $fillable = [
        'name',
        'email',
        'telephone',
        'address_line_1',
        'address_line_2',
        'city',
        'county',
        'postcode',
        'country',
        'address_old',
    ];

    public function donations(): HasMany
    {
        return $this->hasMany(CcDonation::class, 'donor_id');
    }
}
