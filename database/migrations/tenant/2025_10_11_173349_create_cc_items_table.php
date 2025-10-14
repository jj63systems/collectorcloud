<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cc_items', function (Blueprint $table) {
            $table->id();

            // --- Core identifiers ---
            $table->foreignId('team_id')->constrained('cc_teams');
            $table->string('name', 20); // Short label
            $table->string('item_key')->nullable(); // Optional: e.g. accession ref or barcode

            // --- Relationships ---
            $table->foreignId('donation_id')->nullable()->constrained('cc_donations')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('cc_locations')->nullOnDelete();

            // --- Dates and users ---
            $table->date('date_received')->nullable();
            $table->date('accessioned_at')->nullable();
            $table->foreignId('accessioned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // --- Descriptions & notes ---
            $table->text('description')->nullable();
            $table->string('filing_reference')->nullable();
            $table->text('condition_notes')->nullable();
            $table->text('curation_notes')->nullable(); // Optional

            // --- Lifecycle flags ---
            $table->boolean('disposed')->nullable();
            $table->date('disposed_date')->nullable();
            $table->text('disposed_notes')->nullable();

            // --- Optional status fields ---
            $table->string('inventory_status')->nullable(); // e.g. Missing, Found, etc.
            $table->boolean('is_public')->default(false);

            // --- Dynamic field space ---
            foreach (range(1, 999) as $i) {
                $table->text(sprintf('f%03d', $i))->nullable();
            }

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_items');
    }
};
