<?php

namespace App\Models;

use App\Services\DatabaseManager;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;


class Tenant extends BaseTenant
{

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
            Log::warning("No database name set for tenant ID {$this->id}. Skipping drop.");
            return;
        }

        $manager = app(DatabaseManager::class);
        $success = $manager->dropDatabase($dbName);

        if (!$success) {
            Log::warning("Failed to drop database for tenant: $dbName");
        }
    }


}
