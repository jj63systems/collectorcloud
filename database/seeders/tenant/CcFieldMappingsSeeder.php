<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\CcFieldMapping;
use App\Models\Tenant\CcTeam;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CcFieldMappingsSeeder extends Seeder
{
    public function run(): void
    {
        $teams = CcTeam::all();

        if ($teams->isEmpty()) {
            $this->command?->warn('No teams found — skipping CcFieldMappingsSeeder.');
            return;
        }

        foreach ($teams as $team) {
            self::seedForTeam($team->id, true); // true = verbose output when seeding
        }

        $this->command?->info('✅ CcFieldMappingsSeeder completed.');
    }

    /**
     * Seed 100 default field mappings for a specific team.
     *
     * This method can be called from:
     *  - The seeder itself (as above)
     *  - Runtime logic (e.g., Field Mappings screen)
     */
    public static function seedForTeam(int $teamId, bool $logToConsole = false): void
    {
        if (CcFieldMapping::where('team_id', $teamId)->exists()) {
            if ($logToConsole) {
                Log::info("Field mappings already exist for team {$teamId}, skipping seeding.");
            }
            return;
        }

        $rows = [];
        for ($i = 1; $i <= 100; $i++) {
            $field = 'f'.str_pad($i, 3, '0', STR_PAD_LEFT);

            $rows[] = [
                'team_id' => $teamId,
                'field_name' => $field,
                'label' => null,
                'data_type' => 'TEXT',
                'max_length' => null,
                'lookup_type_id' => null,
                'display_seq' => $i * 10,
                'is_required' => false,
                'is_searchable' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        CcFieldMapping::insert($rows);

        if ($logToConsole) {
            Log::info("✅ Seeded 100 field mappings for team ID {$teamId}.");
        }
    }
}
