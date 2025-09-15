<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cc_locations', function (Blueprint $table) {
            $table->unique('path');
        });
    }

    public function down(): void
    {
        Schema::table('cc_locations', function (Blueprint $table) {
            $table->dropUnique(['path']);
        });
    }
};
