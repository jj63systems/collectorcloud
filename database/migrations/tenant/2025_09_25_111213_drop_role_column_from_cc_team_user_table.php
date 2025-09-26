<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cc_team_user', function (Blueprint $table) {
            if (Schema::hasColumn('cc_team_user', 'role')) {
                $table->dropColumn('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cc_team_user', function (Blueprint $table) {
            $table->string('role')->nullable()->after('user_id');
        });
    }
};
