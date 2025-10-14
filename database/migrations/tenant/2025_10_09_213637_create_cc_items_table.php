<?php

use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
//    public function up(): void
//    {
//        Schema::create('cc_items', function (Blueprint $table) {
//            $table->id();
//            $table->string('name', 20);
//            $table->foreignId('team_id')->constrained('cc_teams')->cascadeOnDelete();
//            $table->date('accessioned_at')->nullable();
//            $table->foreignId('accessioned_by')->nullable()->constrained('users')->nullOnDelete();
////            $table->foreignId('donor_id')->nullable()->constrained('cc_donors')->nullOnDelete();
//            $table->text('description')->nullable();
//
//            // Add f001 to f100 as nullable TEXT fields
//            for ($i = 1; $i <= 100; $i++) {
//                $field = 'f'.str_pad((string) $i, 3, '0', STR_PAD_LEFT);
//                $table->text($field)->nullable();
//            }
//
//            $table->timestamps();
//        });
//    }
//
//    public function down(): void
//    {
//        Schema::dropIfExists('cc_items');
//    }
};
