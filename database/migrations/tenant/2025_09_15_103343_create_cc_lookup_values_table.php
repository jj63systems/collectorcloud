<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cc_lookup_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')
                ->constrained('cc_lookup_types')
                ->onDelete('cascade');
            $table->string('code');                     // e.g. MALE
            $table->string('label');                    // e.g. Male
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('system_flag')->default(false)
                ->comment('Protect system-defined values from edit/delete');
            $table->boolean('enabled')->default(true);
            $table->string('color', 20)->nullable()->after('label');

            $table->timestamps();

            $table->unique(['type_id', 'code']);
            $table->index(['type_id', 'enabled', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_lookup_values');
    }
};
