<?php

namespace App\Services;

use App\Models\Tenant\CcTeam;
use Illuminate\Support\Facades\Auth;

class TeamContext
{
    protected static ?CcTeam $cachedTeam = null;

    public static function getCurrentTeam(): ?CcTeam
    {
        if (self::$cachedTeam) {
            return self::$cachedTeam;
        }

        $user = Auth::user();

        if (!$user || !$user->current_team_id) {
            return null;
        }

        return self::$cachedTeam = \App\Models\Tenant\CcTeam::find($user->current_team_id);
    }

    public static function getCurrentTeamId(): ?int
    {
        return self::getCurrentTeam()?->id;
    }
}
