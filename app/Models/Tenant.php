<?php

namespace App\Models;

use App\Services\DatabaseManager;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;


class Tenant extends BaseTenant
{

//    use UsesLandlordConnection;

    protected $fillable = [
        'id',
        'database',
        'name',
        'domain',
        // any other fields you're setting via create()
    ];

    protected static function booted()
    {
        static::creating(fn(Tenant $model) => $model->createDatabase());
        // Delete the tenant database after the Tenant record is deleted
        static::deleted(fn(Tenant $model) => $model->dropDatabase());
        // run all tenant migrations
        static::created(fn(Tenant $model) => $model->runMigrations());

    }

    public function createDatabase(): void
    {
        $dbName = $this->database ?? $this->id; // Use a `database` column or fallback to `id`

        if (!$dbName) {
            throw new \RuntimeException('Tenant must have a database name before creation.');
        }

        $manager = app(DatabaseManager::class);

        $success = $manager->createDatabase($dbName);

        if (!$success) {
            throw new \RuntimeException("Failed to create database for tenant: $dbName");
        }
    }


    public function dropDatabase(): void
    {
        $dbName = $this->database ?? $this->id;

        if (!$dbName) {
            $message = "No database name set for tenant ID {$this->id}.";
            Log::warning($message);
            throw new RuntimeException($message);
        }

        $manager = app(DatabaseManager::class);
        $success = $manager->dropDatabase($dbName);

        if (!$success) {
            $message = "Failed to drop database for tenant: {$dbName}";
            Log::error($message);
            throw new RuntimeException($message); // Propagate to caller
        }
    }


    public function runMigrations(): void
    {
        // Set the tenant as the current tenant — switches DB connection
        $this->makeCurrent();

        // Run migrations on the tenant DB

        app(config('multitenancy.actions.migrate_tenant'))->execute($this);

//        Artisan::call('migrate', [
//            '--path' => 'database/migrations/tenant',
//            '--database' => 'tenant', // this must match your config/database.php connection
//            '--force' => true,
//        ]);
    }


}
