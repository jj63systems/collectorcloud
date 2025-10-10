<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\CcItem;
use App\Models\Tenant\CcTeam;
use Illuminate\Database\Seeder;

class TestItemSeeder extends Seeder
{
    public function run(): void
    {
        $team = CcTeam::first();

        if (!$team) {
            $this->command->warn('No team found — skipping TestItemSeeder.');
            return;
        }

        foreach (range(1, 20) as $i) {
            CcItem::create([
                'name' => 'Item '.str_pad($i, 3, '0', STR_PAD_LEFT),
                'description' => 'Description for test item '.$i,
                'team_id' => $team->id,
                'accessioned_at' => now()->subDays(20 - $i),
                'accessioned_by' => null,
            ]);
        }

        $this->command->info('✅ 20 test items seeded for team ID '.$team->id);
    }
}
