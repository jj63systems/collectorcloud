<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CcSettingGroup extends Model
{
    use UsesTenantConnection;

    protected $table = 'cc_setting_groups';

    protected $fillable = [
        'name',
        'display_seq',
    ];

    public function settings()
    {
        return $this->hasMany(CcSetting::class, 'setting_group_id');
    }
}
