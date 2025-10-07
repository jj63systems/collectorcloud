<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cc_lookup_value_team', function (Blueprint $table) {
            $table->id();

            // Foreign key to lookup values
            $table->foreignId('lookup_value_id')
                ->constrained('cc_lookup_values')
                ->cascadeOnDelete();

            // Foreign key to teams
            $table->foreignId('team_id')
                ->constrained('cc_teams')
                ->cascadeOnDelete();

            // Optional metadata
            $table->boolean('is_default')->default(false)
                ->comment('Marks a team’s default value for this lookup type');

            $table->jsonb('meta')->nullable()
                ->comment('Optional team-specific overrides such as colour, alias, etc.');

            $table->timestamps();

            // Constraints / indexes
            $table->unique(['lookup_value_id', 'team_id']);
            $table->index(['team_id', 'lookup_value_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_lookup_value_team');
    }
};
