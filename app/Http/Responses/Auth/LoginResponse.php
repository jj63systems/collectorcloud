<?php

namespace App\Http\Responses\Auth;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Log;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {

        Log::info('LoginResponse executed');
//        $user = Auth::user();
        $user = $request->user('tenant');

        if (!$user) {
            Log::info('User not found');
            return redirect()->route('filament.app.auth.login');
        }

        Log::info('User logged in: '.$user->email);
        // Case 1: Already has current team
        if ($user->current_team_id) {
            return redirect()->intended(route('filament.app.pages.dashboard'));
        }

        // Case 2 & 3 & 4: No current team
        $teams = $user->teams()->pluck('cc_teams.id', 'cc_teams.name'); // safer keying

        if ($teams->count() === 1) {
            // Case 2: exactly one team
            $teamId = $teams->values()->first(); // get the ID
            $user->update(['current_team_id' => $teamId]);

            return redirect()->intended(route('filament.app.pages.dashboard'));
        }

        if ($teams->count() > 1) {
            // Case 3: multiple teams
            return redirect()->route('filament.app.pages.team-select');
        }

        // Case 4: no teams
        return redirect()->route('no-team');
    }
}
