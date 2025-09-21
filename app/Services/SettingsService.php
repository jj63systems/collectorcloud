<?php

namespace App\Services;

use App\Models\Tenant\CcSetting;
use Illuminate\Support\Facades\Log;

class SettingsService
{
    public static function get(string $code, $default = null): mixed
    {
        return CcSetting::where('setting_code', $code)->value('setting_value') ?? $default;
    }

    public static function isMfaRequired(): bool
    {
        // Look up setting; assume "false" if not set
        Log::info('isMfaRequired: '.self::get('mfa_required', false));
        return filter_var(self::get('', false), FILTER_VALIDATE_BOOLEAN);
    }
}
