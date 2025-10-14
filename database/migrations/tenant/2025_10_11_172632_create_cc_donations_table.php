<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cc_donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('cc_teams');
            $table->foreignId('donor_id')->nullable()->constrained('cc_donors')->nullOnDelete();

            $table->string('donation_name')->nullable();
            $table->date('date_received')->nullable();
            $table->string('donation_basis')->nullable(); // e.g. gift, loan, etc.
            $table->text('comments')->nullable();

            $table->foreignId('accessioned_by')->nullable()->constrained('users')->nullOnDelete();

            // Legacy import support
            $table->string('donation_basis_old')->nullable();
            $table->string('accessioned_by_old')->nullable();
            $table->string('donor_key_old')->nullable();
            $table->string('year_received_old')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_donations');
    }
};
