<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cc_label_overrides', function (Blueprint $table) {
            $table->id();

            // Locale
            $table->string('locale', 12)->default('en');

            // Key and value
            $table->text('key');   // e.g. resources.cc_locations.fields.name
            $table->text('value'); // e.g. "Gallery"

            $table->timestamps();

            $table->unique(['locale', 'key'], 'uniq_label_override');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_label_overrides');
    }
};
