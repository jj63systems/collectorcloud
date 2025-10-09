<?php

use App\Models\Tenant\CcSetting;
use App\Services\LabelService;


if (!function_exists('mylabel')) {
    /**
     * Resolve a label for a given resource + key.
     *
     * Example:
     *   mylabel('cc_locations', 'fields.name', 'Location Name')
     *   mylabel('cc_locations', 'fields.name') // default optional
     */
    function mylabel(string $resourceCode, string $key, ?string $default = null): string
    {
        // If no hard default provided, use the key itself
        $hardDefault = $default ?? $key;

        return app(LabelService::class)->get($resourceCode, $key, $hardDefault);
    }
}

if (!function_exists('ccsetting')) {
    /**
     * Get or set a system setting by its code.
     *
     * Example:
     *   ccsetting('color_scheme')             // get
     *   ccsetting('color_scheme', 'blue')     // set
     */
    function ccsetting(string $code, mixed $value = null, mixed $default = null): mixed
    {
        // --- SET ---
        if (!is_null($value)) {
            CcSetting::query()
                ->where('setting_code', $code)
                ->update([
                    'setting_value' => is_array($value)
                        ? json_encode($value)
                        : $value,
                ]);

            return $value;
        }

        // --- GET ---
        $setting = CcSetting::query()
            ->where('setting_code', $code)
            ->first();

        if (!$setting) {
            return $default;
        }

        $value = $setting->setting_value ?? $setting->default_value ?? $default;

        // Decode JSON values automatically if necessary
        if (
            is_string($value)
            && (str_starts_with($value, '[') || str_starts_with($value, '{'))
        ) {
            try {
                return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                return $value;
            }
        }

        return $value;
    }
}
