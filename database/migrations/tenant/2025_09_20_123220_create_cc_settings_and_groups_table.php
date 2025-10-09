<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cc_setting_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();           // e.g. 'appearance'
            $table->string('label');                    // e.g. 'Appearance'
            $table->integer('display_seq')->default(0);
            $table->timestamps();
        });

        Schema::create('cc_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setting_group_id')
                ->constrained('cc_setting_groups')
                ->cascadeOnDelete();

            $table->string('setting_code')->index();        // e.g. 'language'
            $table->string('setting_label');                // e.g. 'Language'
            $table->text('setting_value')->nullable();      // Current saved value
            $table->text('default_value')->nullable();      // Default from config

            $table->string('value_presentation')->default('text'); // e.g. 'boolean', 'select', etc.
            $table->text('description')->nullable();        // User-facing help text
            $table->jsonb('options_json')->nullable();      // Select / multi-select options
            $table->integer('display_seq')->default(0);     // Order within group

            $table->timestamps();

            $table->unique(['setting_group_id', 'setting_code']); // Prevent duplicates per group
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_settings');
        Schema::dropIfExists('cc_setting_groups');
    }
};
