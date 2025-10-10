<?php

namespace Database\Seeders\tenant;

use App\Models\Tenant\CcFieldMapping;
use App\Models\Tenant\CcTeam;
use Illuminate\Database\Seeder;

class CcFieldMappingsSeeder extends Seeder
{
    public function run(): void
    {
        $teams = CcTeam::all();

        if ($teams->isEmpty()) {
            $this->command->warn('No teams found — skipping CcFieldMappingsSeeder.');
            return;
        }

        foreach ($teams as $team) {
            $this->command->info("Seeding 100 field mappings for team: {$team->name}");

            $rows = [];
            for ($i = 1; $i <= 100; $i++) {
                $field = 'f'.str_pad($i, 3, '0', STR_PAD_LEFT);

                $rows[] = [
                    'team_id' => $team->id,
                    'field_name' => $field,
                    'label' => null, // not active until customised
                    'data_type' => 'TEXT',
                    'max_length' => null,
                    'lookup_type_id' => null,
                    'display_seq' => $i,
                    'is_required' => false,
                    'is_searchable' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Use bulk insert for performance
            CcFieldMapping::insert($rows);
        }

        $this->command->info('✅ CcFieldMappingsSeeder completed.');
    }
}
