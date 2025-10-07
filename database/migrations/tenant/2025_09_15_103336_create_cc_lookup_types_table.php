<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cc_lookup_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();           // e.g. GENDER
            $table->string('name');                     // e.g. Gender

            $table->foreignId('parent_lookup_type_id')
                ->nullable()
                ->constrained('cc_lookup_types')
                ->nullOnDelete()
                ->comment('Optional parent type for hierarchical grouping');

            $table->boolean('is_team_scoped')->default(false)
                ->comment('If true, lookup values are restricted per team via pivot');

            $table->timestamps();

            $table->index(['parent_lookup_type_id']);
            $table->index(['is_team_scoped']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_lookup_types');
    }
};
