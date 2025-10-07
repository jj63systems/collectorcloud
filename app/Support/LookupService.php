<?php

namespace App\Support;

use App\Models\Tenant\CcLookupType;
use App\Models\Tenant\CcLookupValue;

class LookupService
{
    public static function options(string $typeCode, ?int $teamId = null)
    {
        $type = CcLookupType::query()->where('code', $typeCode)->firstOrFail();

        $q = CcLookupValue::query()
            ->where('type_id', $type->id)
            ->enabled()
            ->orderBy('sort_order')
            ->orderBy('label');

        if ($type->is_team_scoped && $teamId) {
            $q->whereHas('teams', fn($t) => $t->whereKey($teamId));
        }

        return $q->pluck('label', 'id');
    }
}
