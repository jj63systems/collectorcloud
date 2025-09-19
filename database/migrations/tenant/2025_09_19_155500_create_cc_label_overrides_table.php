<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cc_label_overrides', function (Blueprint $table) {
            $table->id();

            $table->foreignId('team_id')->constrained('cc_teams')->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained('cc_resources')->cascadeOnDelete();

            // Locale
            $table->string('locale', 12)->default('en');
            // Key and value
            $table->text('key');   // e.g. resources.cc_locations.fields.name
            $table->text('value'); // e.g. "Gallery"

            $table->timestamps();

            $table->unique(['team_id', 'resource_id', 'locale', 'key'], 'uniq_cc_label_override');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_label_overrides');
    }
};
