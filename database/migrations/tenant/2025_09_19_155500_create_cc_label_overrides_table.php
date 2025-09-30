<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cc_label_overrides', function (Blueprint $table) {
            $table->id();

            $table->foreignId('team_id')->nullable()->constrained('cc_teams');
            $table->foreignId('resource_id')->constrained('cc_resources')->cascadeOnDelete();

            // Locale
            $table->string('locale', 12)->default('en');
            // Key and value
            $table->text('key');   // e.g. resources.cc_locations.fields.name
            $table->text('value'); // e.g. "Gallery"

            $table->timestamps();

            // note: unique constraints are added via DB::statement() below
        });

        // One global override max (team_id IS NULL)
        DB::statement("
            CREATE UNIQUE INDEX uniq_cc_label_override_global
            ON cc_label_overrides (resource_id, locale, key)
            WHERE team_id IS NULL
        ");

        // One per-team override max (team_id IS NOT NULL)
        DB::statement("
            CREATE UNIQUE INDEX uniq_cc_label_override_team
            ON cc_label_overrides (team_id, resource_id, locale, key)
            WHERE team_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS uniq_cc_label_override_global");
        DB::statement("DROP INDEX IF EXISTS uniq_cc_label_override_team");

        Schema::dropIfExists('cc_label_overrides');
    }
};
