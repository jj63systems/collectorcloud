<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cc_lookup_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained('cc_lookup_types')->onDelete('cascade');
            $table->string('code');        // e.g. MALE
            $table->string('label');       // e.g. Male
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('system_flag')->default(false); // prevent user deletion/edit
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['type_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_lookup_values');
    }
};
