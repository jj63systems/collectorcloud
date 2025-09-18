<?php

use App\Services\LabelService;

if (!function_exists('mylabel')) {
    function mylabel(string $key, ?string $default = null): string
    {
        return app(LabelService::class)->get($key, $default);
    }
}
