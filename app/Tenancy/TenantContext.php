<?php

namespace App\Tenancy;

use App\Central\Models\Tenant;
use RuntimeException;

/**
 * Singleton that holds the currently resolved Tenant for the lifetime of a
 * single HTTP request or queue job.  Inject this class wherever you need to
 * read the active tenant without going back to the database.
 */
class TenantContext
{
    private ?Tenant $tenant = null;

    // -------------------------------------------------------------------------
    // Mutators
    // -------------------------------------------------------------------------

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function forget(): void
    {
        $this->tenant = null;
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Return the current tenant, throwing if tenancy has not been initialised.
     *
     * @throws RuntimeException
     */
    public function get(): Tenant
    {
        if ($this->tenant === null) {
            throw new RuntimeException(
                'No tenant in context. Was the IdentifyTenant middleware applied to this route?'
            );
        }

        return $this->tenant;
    }

    /**
     * Return the current tenant or null when running outside a tenant context
     * (e.g. on the central domain or in some Artisan commands).
     */
    public function getOrNull(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * Whether a tenant has been set for the current lifecycle.
     */
    public function check(): bool
    {
        return $this->tenant !== null;
    }
}
