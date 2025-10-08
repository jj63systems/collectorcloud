<?php

namespace App\Filament\Traits;

use App\Models\Tenant\CcResource;
use App\Models\Tenant\CcTeam;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait HasResourcePermissions
{
    protected static ?CcTeam $cachedTeam = null;
    protected static ?CcResource $cachedResource = null;

    protected static function checkPermission(string $action): bool
    {
//        Log::info('checkPermission: '.$action);

        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if ($user->is_superuser ?? false) {
//            Log::info('Superuser');
            return true;
        }

//        Log::info($user);

        $resourceKey = static::$resourceKey ?? static::getResourceKey();
//        Log::info('Resource key:'.$resourceKey);

        return $user->hasPermissionTo("{$resourceKey}.{$action}", 'tenant');
    }

    public static function canViewAny(): bool
    {
        return static::checkPermission('view');
    }

    public static function canCreate(): bool
    {
        Log::info('canCreate');
        return static::checkPermission('create');
    }

    public static function canEdit($record): bool
    {
        return static::checkPermission('update');
    }

    public static function canDelete($record): bool
    {
        return static::checkPermission('delete');
    }

    protected static function getResourceKey(): string
    {
        return str(static::class)
            ->classBasename()
            ->beforeLast('Resource')
            ->snake()
            ->plural()
            ->toString();
    }

    /**
     * Optional helper – if you need to access the current team efficiently
     */
    protected static function getCurrentTeam(): ?CcTeam
    {
        return once(function () {
            $team = Auth::user()?->currentTeam;
            Log::info('Memoized current team', ['team_id' => $team?->id]);
            return $team;
        });
    }

    /**
     * Optional helper – if you need to access the current resource efficiently
     */
    protected static function getCurrentResource(): ?CcResource
    {
        return once(function () {
            $code = property_exists(static::class, 'resourceKey')
                ? static::$resourceKey
                : static::getResourceKey();

            $resource = \App\Models\Tenant\CcResource::where('code', $code)->first();
            \Log::info('Memoized current resource', ['code' => $code, 'id' => $resource?->id]);

            return $resource;
        })(); // <- CALL the closure returned by once()
    }
}
