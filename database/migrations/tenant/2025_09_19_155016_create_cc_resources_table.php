<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cc_resources', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();   // e.g. "cc_locations"
            $table->string('name');            // e.g. "Locations"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_resources');
    }
};
