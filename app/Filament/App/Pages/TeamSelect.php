<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TeamSelect extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-users';
    protected static ?string $slug = 'team-select';
    protected string $view = 'filament.app.pages.team-select';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public ?array $teams = [];

    public function mount(): mixed
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('filament.app.auth.login');
        }

        $this->teams = $user->teams()
            ->pluck('cc_teams.name', 'cc_teams.id')
            ->toArray();

        Log::info('teams', $this->teams);

        // Auto-assign if no team at all
        if (count($this->teams) === 0) {
            return redirect()->route('no-team');
        }

        // Auto-assign if only one team
        if (count($this->teams) === 1) {
            $teamId = array_key_first($this->teams);
            $user->update(['current_team_id' => $teamId]);

            return redirect()->route('filament.app.pages.dashboard'); // straight to dashboard
        }

        // else show selection UI
        return null;
    }

    public function selectTeam(int $teamId)
    {
        $user = Auth::user();

        if ($user && array_key_exists($teamId, $this->teams)) {
            $user->update(['current_team_id' => $teamId]);
        }

        return $this->redirectRoute('filament.app.pages.dashboard');
    }
}
