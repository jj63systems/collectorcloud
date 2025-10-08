<?php

namespace App\Filament\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait HasNavigationPermission
{
    public static function shouldRegisterNavigation(): bool
    {
//        Log::info('Starting check');
        $user = Auth::user();

        if (!$user) {
            Log::info('No user');
            return false;
        }

        // Always show nav for superusers
        if ($user->is_superuser ?? false) {
//            Log::info('Superuser');
            return true;
        }

        $permission = static::getNavigationPermission();
        $hasPermission = $user->hasPermissionTo($permission, 'tenant');

//        Log::info("Checking permission: $permission => ".($hasPermission ? 'true' : 'false'));
        // ✅ Explicit guard-aware check
        $result = (bool) $user->hasPermissionTo($permission, 'tenant');

//        Log::info("Return value = ".var_export($result, true));

        return $result;
    }


    /**
     * Override this in your resource or provide a sensible default.
     */
    protected static function getNavigationPermission(): string
    {
        // Default guess: resource name + ".view"
        return static::getModel()::class.'.view';
    }
}
