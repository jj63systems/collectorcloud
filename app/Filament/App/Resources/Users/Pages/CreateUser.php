<?php

namespace App\Filament\App\Resources\Users\Pages;

use App\Filament\App\Resources\Users\UserResource;
use App\Models\Tenant\TenantUser;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Password;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** @var TenantUser */
    public ?\Illuminate\Database\Eloquent\Model $record;

    protected function afterCreate(): void
    {
        // Generate a password reset token for the new user (tenant broker)
        $token = Password::broker('tenant_users')->createToken($this->record);

        // Send the "set password" email notification
        $this->record->sendPasswordSetupNotification($token);
    }
}
