<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('team_allowed_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('team_id')
                ->references('id')->on('cc_teams')
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')->on('roles')
                ->onDelete('cascade');

            $table->primary(['team_id', 'role_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_allowed_roles');
    }
};
