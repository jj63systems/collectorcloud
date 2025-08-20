<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PDOException;

class DatabaseManager
{
    protected string $adminConnection;

    public function __construct()
    {
        // Uses the default DB connection defined in .env as DB_CONNECTION (e.g., 'landlord')
        $this->adminConnection = config('database.default');
    }

    public function createDatabase(string $databaseName): bool
    {
        try {
            $safeName = Str::of($databaseName)->replace('"', '')->value();

            DB::connection($this->adminConnection)
                ->statement("CREATE DATABASE \"$safeName\"");

            Log::info("Database created: $databaseName");

            return true;
        } catch (PDOException $e) {
            Log::error("Database creation failed for $databaseName: " . $e->getMessage());

            return false;
        }
    }

    public function dropDatabase(string $databaseName): bool
    {
        try {
            $safeName = Str::of($databaseName)->replace('"', '')->value();

            DB::connection($this->adminConnection)
                ->statement("DROP DATABASE IF EXISTS \"$safeName\"");

            Log::info("Database dropped: $databaseName");

            return true;
        } catch (PDOException $e) {
            Log::error("Database drop failed for $databaseName: " . $e->getMessage());

            return false;
        }
    }
}
