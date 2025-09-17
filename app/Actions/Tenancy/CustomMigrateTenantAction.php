<?php

namespace App\Actions\Tenancy;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Actions\MigrateTenantAction;
use Spatie\Multitenancy\Contracts\IsTenant;

class CustomMigrateTenantAction extends MigrateTenantAction
{
    public function execute(IsTenant $tenant): static
    {
        // Switch DB context to the current tenant
        $tenant->makeCurrent();

        Log::info('Running tenant migration', [
            'tenantId' => $tenant->getKey(),
            'database' => DB::connection('tenant')->getDatabaseName(),
        ]);

        Artisan::call('migrate', [
            '--path' => 'database/migrations/tenant',
            '--database' => 'tenant',
            '--force' => true,
        ]);

        // Use tenants:artisan with --tenant to constrain to this tenant only
        Artisan::call('tenants:artisan', [
            'artisanCommand' => "db:seed --class=Database\\Seeders\\tenant\\TenantDatabaseSeeder --database=tenant --force",
            '--tenant' => [$tenant->getKey()],
        ]);

        Log::info('Finished seeding tenant DB', [
            'tenantId' => $tenant->getKey(),
            'database' => DB::connection('tenant')->getDatabaseName(),
        ]);

        return $this;
    }
}
