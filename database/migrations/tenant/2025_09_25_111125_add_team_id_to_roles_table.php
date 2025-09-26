<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable()->after('id');

            $table->foreign('team_id')
                ->references('id')->on('cc_teams')
                ->cascadeOnDelete();

            $table->index('team_id', 'roles_team_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropIndex('roles_team_id_index');
            $table->dropColumn('team_id');
        });
    }
};
