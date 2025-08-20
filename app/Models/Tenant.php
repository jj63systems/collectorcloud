<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Tenant as BaseTenant;
use App\Services\DatabaseManager;


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
    }

    public function createDatabase(): void
    {
        $dbName = $this->database ?? $this->id; // Use a `database` column or fallback to `id`

        if (! $dbName) {
            throw new \RuntimeException('Tenant must have a database name before creation.');
        }

        $manager = app(DatabaseManager::class);

        $success = $manager->createDatabase($dbName);

        if (! $success) {
            throw new \RuntimeException("Failed to create database for tenant: $dbName");
        }
    }
}
