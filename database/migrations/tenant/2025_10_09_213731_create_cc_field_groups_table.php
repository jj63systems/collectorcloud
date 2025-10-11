<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cc_field_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')
                ->constrained('cc_teams')
                ->onDelete('cascade');
            $table->string('name');
            $table->boolean('is_protected')->default(false);
            $table->integer('display_seq')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_field_groups');
    }
};
