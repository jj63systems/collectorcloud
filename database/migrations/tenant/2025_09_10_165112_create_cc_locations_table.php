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

            $table->string('type')->nullable();   // e.g. Room, Cupboard
            $table->string('code')->nullable();   // e.g. "RM-01"
            $table->unsignedInteger('depth')->nullable(); // e.g. 0 = root
            $table->string('path')->nullable();   // Optional materialized path

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_locations');
    }
};
