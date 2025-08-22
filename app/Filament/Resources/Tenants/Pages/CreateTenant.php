<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\TenantResource;
use App\Models\tenant\TenantUser;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;


class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected array $initialUserData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->initialUserData = [
            'name' => $data['user_name'] ?? null,
            'email' => $data['user_email'] ?? null,
            'password' => isset($data['user_password']) ? Hash::make($data['user_password']) : null,
        ];

        unset($data['user_name'], $data['user_email'], $data['user_password']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // create the requested user in the tenant database
        TenantUser::create($this->initialUserData);
    }

    protected function createTenantDatabase(string $dbName): void
    {
        // Example: create the DB if it doesn’t exist (PostgreSQL-safe, tweak for MySQL)
        \DB::connection('central')->statement("CREATE DATABASE IF NOT EXISTS \"$dbName\"");
    }
}
