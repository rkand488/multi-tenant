<?php

use App\Central\Models\Tenant;
use App\Tenancy\TenantContext;

if (! function_exists('tenant')) {
    /**
     * Return the currently resolved Tenant for this request / job.
     *
     * Throws a RuntimeException when called outside of a tenant context,
     * so prefer tenantOrNull() in code that may run on the central domain.
     */
    function tenant(): Tenant
    {
        return app(TenantContext::class)->get();
    }
}

if (! function_exists('tenantOrNull')) {
    /**
     * Return the currently resolved Tenant or null when running on the
     * central domain or in a console command without an active tenant.
     */
    function tenantOrNull(): ?Tenant
    {
        return app(TenantContext::class)->getOrNull();
    }
}

if (! function_exists('tenantCache')) {
    /**
     * Return a cache repository whose keys are automatically scoped to the
     * current tenant via a tag, preventing cross-tenant data leakage.
     *
     * Requires a cache driver that supports tagging (Redis, Memcached).
     */
    function tenantCache(): Illuminate\Cache\Repository
    {
        return cache()->tags(['tenant:'.tenant()->id]);
    }
}
