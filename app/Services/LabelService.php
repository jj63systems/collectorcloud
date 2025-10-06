<?php

namespace App\Services;

use App\Models\Tenant\CcLabelOverride;
use Illuminate\Support\Facades\Log;

class LabelService
{
    /**
     * In-request cache of labels, keyed by team + resource + locale.
     */
    protected array $cache = [];

    /**
     * Resolve a label key into its text.
     */
    public function get(string $resourceCode, string $key, string $hardDefault): string
    {
        $locale = app()->getLocale();

        // Find resource
        $resourceId = \App\Services\ResourceContext::getResourceId($resourceCode);
        if (!$resourceId) {
            return $hardDefault;
        }

        // Get current user + team
        $teamId = \App\Services\TeamContext::getCurrentTeamId();

        // Cache key
        $cacheKey = "resource:{$resourceId}:locale:{$locale}:team:{$teamId}";

        // Load if not already cached
        if (!isset($this->cache[$cacheKey])) {
            $this->cache[$cacheKey] = $this->loadLabels($resourceId, $teamId, $locale, $resourceCode);
        }

        // Return resolved label or fallback
        return $this->cache[$cacheKey][$key] ?? $hardDefault;
    }

    /**
     * Bulk load labels for a resource, considering team + tenant + lang file.
     *
     * @return array<string,string>
     */
    protected function loadLabels(int $resourceId, ?int $teamId, string $locale, string $resourceCode): array
    {
        // 1. Language file defaults (lowest precedence)
        $defaults = collect(trans("resources.$resourceCode", [], $locale));
        $langLabels = $defaults->dot()->mapWithKeys(fn($value, $key) => [$key => $value])->toArray();
        $labels = $langLabels;

        // 2. Tenant-wide overrides (higher precedence)
        $tenantOverrides = CcLabelOverride::query()
            ->whereNull('team_id')
            ->where('resource_id', $resourceId)
            ->where('locale', $locale)
            ->pluck('value', 'key')
            ->toArray();
        $labels = array_merge($labels, $tenantOverrides);

        // 3. Team-level overrides (highest precedence)
        $teamOverrides = [];
        if ($teamId) {
            $teamOverrides = CcLabelOverride::query()
                ->where('team_id', $teamId)
                ->where('resource_id', $resourceId)
                ->where('locale', $locale)
                ->pluck('value', 'key')
                ->toArray();
            $labels = array_merge($labels, $teamOverrides);
        }

        // Debug logging
        Log::info("LabelService [$resourceCode] lang defaults:", $langLabels);
        Log::info("LabelService [$resourceCode] tenant overrides:", $tenantOverrides);
        Log::info("LabelService [$resourceCode] team overrides:", $teamOverrides);
        Log::info("LabelService [$resourceCode] final merged:", $labels);

        return $labels;
    }
}
