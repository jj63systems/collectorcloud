<?php

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
