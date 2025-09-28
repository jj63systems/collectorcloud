<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clear Spatie permission cache
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Permissions table
        Schema::create('permissions', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        // Roles table
        Schema::create('roles', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        // model_has_permissions pivot
        Schema::create('model_has_permissions', static function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');

            $table->primary(['permission_id', 'model_id', 'model_type'],
                'model_has_permissions_permission_model_type_primary');
        });

        // model_has_roles pivot
        Schema::create('model_has_roles', static function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

            $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
        });

        // role_has_permissions pivot
        Schema::create('role_has_permissions', static function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });

        // Optional: clear cache store used by Spatie permission
        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ([
                     'role_has_permissions',
                     'model_has_roles',
                     'model_has_permissions',
                     'roles',
                     'permissions',
                 ] as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }
    }
};
