<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('cc_field_mappings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('team_id')->constrained('cc_teams')->cascadeOnDelete();

            $table->string('field_name', 10); // e.g., f001
            $table->string('label')->nullable(); // If null = not active for this team
            $table->string('data_type', 20)->default('TEXT'); // TEXT, NUMBER, LOOKUP, DATE, etc.
            $table->integer('max_length')->nullable();
            $table->unsignedBigInteger('lookup_type_id')->nullable(); // Optional FK to lookups
            $table->integer('display_seq')->default(0);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_searchable')->default(false);
            $table->text('toggle_option')->default('notoggle');

            $table->timestamps();

            $table->unique(['team_id', 'field_name'], 'uniq_team_field'); // one mapping per team per field

            // Optional FK constraint
            // $table->foreign('lookup_type_id')->references('id')->on('cc_lookup_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_field_mappings');
    }
};
