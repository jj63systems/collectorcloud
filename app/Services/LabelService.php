<?php

namespace App\Services;

use App\Models\Tenant\CcLabelOverride;

class LabelService
{
    /**
     * In-request cache of labels, keyed by resource prefix
     * e.g. "resources.cc_locations"
     */
    protected array $cache = [];

    /**
     * Resolve a label key into its text.
     */
    public function get(string $key, ?string $default = null): string
    {
        $locale = app()->getLocale();

        // Extract prefix: e.g. "resources.cc_locations"
        $parts = explode('.', $key);
        if (count($parts) < 2) {
            return $default ?? $key; // malformed key
        }
        $prefix = $parts[0].'.'.$parts[1];

        // If not already cached, bulk load for this resource
        if (!isset($this->cache[$prefix])) {
            $this->cache[$prefix] = $this->loadResourceLabels($prefix, $locale);
        }

        // Return the requested key if available, else fallback
        return $this->cache[$prefix][$key] ?? $default ?? $key;
    }

    /**
     * Bulk load all labels for a given resource prefix.
     *
     * @return array<string,string>
     */
    protected function loadResourceLabels(string $prefix, string $locale): array
    {
        // Start with defaults from lang file
        $defaults = collect(trans($prefix, [], $locale));
        $labels = $defaults->dot()->mapWithKeys(function ($value, $key) use ($prefix) {
            return [$prefix.'.'.$key => $value];
        })->toArray();

        // Load overrides from DB in one query
        $overrides = CcLabelOverride::query()
            ->where('locale', $locale)
            ->where('key', 'like', $prefix.'%')
            ->pluck('value', 'key')
            ->toArray();

        // Merge: overrides take precedence over defaults
        return array_merge($labels, $overrides);
    }
}
