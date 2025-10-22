<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

return new class extends Migration {
    use UsesTenantConnection;

    public function up(): void
    {
        Schema::create('cc_data_loads', function (Blueprint $table) {
            $table->id();

            // Tenant + team scoping
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();

            // Upload details
            $table->string('filename')->nullable();
            $table->string('worksheet_name')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();

            $table->enum('status', [
                'staged',       // initial upload
                'queued',       // waiting in queue
                'processing',   // actively running job
                'completed',    // finished successfully
                'failed',       // errored
            ])->default('staged')->index();

            $table->string('validation_status')->nullable(); // e.g., pending, validating, complete
            $table->integer('validation_progress')->default(0); // percentage


            $table->integer('row_count')->nullable();
            $table->unsignedInteger('rows_processed')->default(0);

            $table->text('notes')->nullable();
            $table->jsonb('confirmed_field_mappings')->nullable();
            $table->jsonb('sample_rows')->nullable();

            // Standard timestamps
            $table->timestamps();

            // Optional: If you want referential integrity
            // $table->foreign('team_id')->references('id')->on('cc_teams')->cascadeOnDelete();
            // $table->foreign('user_id')->references('id')->on('cc_users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_data_loads');
    }
};
