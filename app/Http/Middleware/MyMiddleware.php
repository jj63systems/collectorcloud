<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('MyMiddleware executed for path: '.$request->path());
//
        $ten = app('currentTenant');
        Log::info($ten);

        return $next($request);
    }
}
