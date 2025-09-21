<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cc_setting_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('display_seq')->default(0);
            $table->timestamps();
        });

        Schema::create('cc_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_code')->index();
            $table->text('setting_value')->nullable();
            $table->foreignId('setting_group_id')
                ->constrained('cc_setting_groups')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_settings');
        Schema::dropIfExists('cc_setting_groups');
    }
};
