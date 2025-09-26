<?php

namespace App\Filament\App\Resources;

use App\Models\Tenant\CcTeam;
use App\Models\Tenant\TenantUser;
use Filament\Facades\Filament;
use Filament\Resources\Resource;

/**
 * BaseResource centralises access control for all Filament resources.
 *
 * Usage:
 *   class CcItemResource extends BaseResource {
 *       protected static string $model = CcItem::class;
 *       protected static string $resourceSlug = 'cc_items';
 *   }
 */
abstract class BaseResource extends Resource
{
    /**
     * Each resource must define its slug, e.g. "cc_items".
     * Used to build permission names: {slug}.view, {slug}.edit, etc.
     */
    protected static string $resourceSlug;

    protected static function user(): ?TenantUser
    {
        /** @var TenantUser|null $user */
        return Filament::auth()->user();
    }

    protected static function currentTeam(): ?CcTeam
    {
        return static::user()?->currentTeam;
    }

    public static function canViewAny(): bool
    {
        $user = static::user();
        return $user?->canAccess(static::$resourceSlug.'.view', static::currentTeam()) ?? false;
    }

    public static function canCreate(): bool
    {
        $user = static::user();
        return $user?->canAccess(static::$resourceSlug.'.create', static::currentTeam()) ?? false;
    }

    public static function canView($record): bool
    {
        $user = static::user();
        return $user?->canAccess(static::$resourceSlug.'.view', static::currentTeam()) ?? false;
    }

    public static function canEdit($record): bool
    {
        $user = static::user();
        return $user?->canAccess(static::$resourceSlug.'.edit', static::currentTeam()) ?? false;
    }

    public static function canDelete($record): bool
    {
        $user = static::user();
        return $user?->canAccess(static::$resourceSlug.'.delete', static::currentTeam()) ?? false;
    }
}
