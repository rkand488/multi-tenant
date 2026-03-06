<?php

namespace App\Http\Middleware;

use App\Central\Models\Tenant;
use App\Tenancy\DatabaseManager;
use App\Tenancy\TenantContext;

/**
 * Queue job middleware that restores the tenant context before a job runs and
 * tears it down afterwards. Intended for jobs that carry a `tenantId` property
 * (e.g. via the TenantAwareJob trait) so that all database interactions inside
 * the job are automatically scoped to the correct tenant.
 *
 * Usage — apply on a job class:
 *
 *   public function middleware(): array
 *   {
 *       return [new InitializeTenancyForQueue];
 *   }
 */
class InitializeTenancyForQueue
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly DatabaseManager $databaseManager,
    ) {}

    /**
     * Handle the job, restoring tenant context around the job's execution.
     */
    public function handle(object $job, callable $next): void
    {
        $tenantId = $job->tenantId ?? null;

        if ($tenantId !== null) {
            $tenant = Tenant::on('central')->findOrFail($tenantId);

            $this->context->set($tenant);
            $this->databaseManager->connectTenant($tenant);
        }

        try {
            $next($job);
        } finally {
            if ($tenantId !== null) {
                $this->context->forget();
                $this->databaseManager->connectCentral();
            }
        }
    }
}
