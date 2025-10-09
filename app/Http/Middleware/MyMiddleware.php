<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('MyMiddleware executed for path: '.$request->path());

        $tenant = app('currentTenant');
        Log::info($tenant ? "Tenant identified: {$tenant->id}" : 'No tenant identified');

        // Retrieve tenant-selected colour scheme (fallback = 'sky')
        $scheme = ccsetting('color_scheme', null, 'sky');
        Log::info("Tenant colour scheme: {$scheme}");

        // Explicit colour map for Filament v4 (using constants, not methods)
        $colors = [
            'amber' => Color::Amber,
            'blue' => Color::Blue,
            'cyan' => Color::Cyan,
            'emerald' => Color::Emerald,
            'gray' => Color::Gray,
            'green' => Color::Green,
            'indigo' => Color::Indigo,
            'lime' => Color::Lime,
            'orange' => Color::Orange,
            'pink' => Color::Pink,
            'purple' => Color::Purple,
            'red' => Color::Red,
            'rose' => Color::Rose,
            'sky' => Color::Sky,
            'slate' => Color::Slate,
            'teal' => Color::Teal,
        ];

        $palette = $colors[$scheme] ?? Color::Sky;

        FilamentColor::register([
            'primary' => $palette,
            // Optionally, define others like:
            // 'success' => Color::Emerald,
            // 'danger'  => Color::Rose,
            // 'warning' => Color::Amber,
        ]);

        return $next($request);
    }
}
