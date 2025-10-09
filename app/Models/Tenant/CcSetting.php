<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CcSetting extends Model
{
    use UsesTenantConnection;

    protected $table = 'cc_settings';

    protected $fillable = [
        'setting_group_id',
        'setting_code',
        'setting_label',
        'setting_value',
        'default_value',
        'value_presentation',
        'description',
        'options_json',
        'display_seq',
    ];

    public function group()
    {
        return $this->belongsTo(CcSettingGroup::class, 'setting_group_id');
    }
}
