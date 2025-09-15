<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cc_locations', function (Blueprint $table) {
            $table->id();

            $table->string('name'); // e.g. "Shelf A"

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('cc_locations')
                ->nullOnDelete();

            $table->foreignId('type_id') // e.g. ROOM, CUPBOARD
            ->nullable()
                ->constrained('cc_lookup_values')
                ->nullOnDelete();

            // removed: $table->string('code')->nullable();

            $table->unsignedInteger('depth')->nullable(); // e.g. 0 = root
            $table->string('path')->nullable();           // materialized path

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_locations');
    }
};
