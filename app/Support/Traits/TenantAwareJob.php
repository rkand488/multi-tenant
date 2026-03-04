<?php

namespace App\Support\Traits;

use App\Central\Models\Tenant;
use App\Tenancy\DatabaseManager;
use App\Tenancy\TenantContext;

/**
 * Stores the current tenant ID on dispatch and restores tenancy context
 * when the job is processed on a queue worker.
 *
 * Usage:
 *   class MyTenantJob implements ShouldQueue
 *   {
 *       use Queueable, TenantAwareJob;
 *
 *       public function handle(): void
 *       {
 *           $this->initializeTenancy();
 *           // ... tenant-scoped work here ...
 *       }
 *   }
 */
trait TenantAwareJob
{
    public ?string $tenantId = null;

    /**
     * Capture the active tenant ID at dispatch time so queue workers can
     * restore the correct tenant context.
     */
    public function captureCurrentTenant(): void
    {
        /** @var TenantContext $context */
        $context = app(TenantContext::class);
        $tenant = $context->getOrNull();

        if ($tenant !== null) {
            $this->tenantId = $tenant->id;
        }
    }

    /**
     * Re-connect the tenant database and populate TenantContext.
     * Call this at the start of handle().
     */
    public function initializeTenancy(): void
    {
        if ($this->tenantId === null) {
            return;
        }

        $tenant = Tenant::on('central')->findOrFail($this->tenantId);

        /** @var TenantContext $context */
        $context = app(TenantContext::class);
        $context->set($tenant);

        app(DatabaseManager::class)->connectTenant($tenant);
    }

    /**
     * Tag the job so Horizon can group & filter by tenant.
     *
     * @return list<string>
     */
    public function tags(): array
    {
        return array_filter(['tenant:'.$this->tenantId]);
    }
}
