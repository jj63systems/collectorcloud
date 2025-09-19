<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cc_team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('cc_teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->nullable(); // optional: e.g. 'admin', 'member'
            $table->timestamps();

            $table->unique(['team_id', 'user_id']); // prevent duplicates
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_team_user');
    }
};
