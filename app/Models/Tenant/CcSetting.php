<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CcSetting extends Model
{
    use UsesTenantConnection;

    protected $table = 'cc_settings';

    protected $fillable = [
        'setting_code',
        'setting_value',
        'setting_group_id',
    ];

    public function group()
    {
        return $this->belongsTo(CcSettingGroup::class, 'setting_group_id');
    }
}
