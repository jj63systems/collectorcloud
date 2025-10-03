<?php

namespace App\Filament\Traits;

use Illuminate\Support\Facades\Auth;

trait HasNavigationPermission
{
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Each resource can define its "base permission"
        $permission = static::getNavigationPermission();

        return $user->can($permission);
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        $permission = static::getNavigationPermission();

        return $user->can($permission);
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
