<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->after('model_id');

            $table->foreign('team_id')
                ->references('id')->on('cc_teams')
                ->cascadeOnDelete();

            // Drop the old PK
            $table->dropPrimary();

            // Add new composite PK including team_id
            $table->primary(['role_id', 'model_id', 'model_type', 'team_id'],
                'model_has_roles_role_model_team_primary');
        });
    }

    public function down(): void
    {
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropPrimary('model_has_roles_role_model_team_primary');

            // Restore original PK
            $table->primary(['role_id', 'model_id', 'model_type']);

            $table->dropColumn('team_id');
        });
    }
};
