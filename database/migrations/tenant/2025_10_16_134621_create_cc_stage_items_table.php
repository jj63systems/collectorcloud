<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cc_items_stage', function (Blueprint $table) {
            $table->id();

            $table->text('team_id')->nullable();
            $table->text('name')->nullable();
            $table->text('item_key')->nullable();

            $table->text('donation_id')->nullable();
            $table->text('location_id')->nullable();

            $table->text('date_received')->nullable();
            $table->text('accessioned_at')->nullable();
            $table->text('accessioned_by')->nullable();
            $table->text('checked_by_user_id')->nullable();

            $table->text('description')->nullable();
            $table->text('filing_reference')->nullable();
            $table->text('condition_notes')->nullable();
            $table->text('curation_notes')->nullable();

            $table->text('disposed')->nullable();
            $table->text('disposed_date')->nullable();
            $table->text('disposed_notes')->nullable();

            $table->text('inventory_status')->nullable();
            $table->text('is_public')->nullable();

            foreach (range(1, 999) as $i) {
                $table->text(sprintf('f%03d', $i))->nullable();
            }

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_items_stage');
    }
};
