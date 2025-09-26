<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\TenantResource;
use App\Models\Tenant\CcTeam;
use App\Models\Tenant\TenantUser;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
            'is_superuser' => true,
        ];

        unset($data['user_name'], $data['user_email'], $data['user_password']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Ensure the tenant has a default team
        $team = CcTeam::firstOrCreate(['name' => 'Default Team']);

        // Create the requested user in the tenant database with current_team_id set
        Log::info("Creating user in tenant: $this->record->id");
        $user = TenantUser::create(array_merge($this->initialUserData, [
            'current_team_id' => $team->id,
        ]));

        // Link user to the team via pivot
        $user->teams()->syncWithoutDetaching([$team->id]);

        // (Optional) Give them the superuser role in this team
        if (!$user->hasRole('superuser', $team)) {
            $user->assignRole('superuser', $team);
        }
    }
}
