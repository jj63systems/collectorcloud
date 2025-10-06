<?php

namespace App\Services;

use App\Models\Tenant\CcResource;

class ResourceContext
{
    protected static array $cache = [];

    public static function getResource(string $resourceCode): ?CcResource
    {
        if (!isset(self::$cache[$resourceCode])) {
            self::$cache[$resourceCode] = CcResource::where('code', $resourceCode)->first();
        }

        return self::$cache[$resourceCode];
    }

    public static function getResourceId(string $resourceCode): ?int
    {
        return self::getResource($resourceCode)?->id;
    }
}
