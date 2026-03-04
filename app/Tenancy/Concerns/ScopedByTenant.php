<?php

namespace App\Tenancy\Concerns;

use App\Tenancy\Scopes\TenantScope;

/**
 * Trait for Eloquent models that are scoped to a single tenant.
 *
 * Adding this trait to a model automatically registers the TenantScope,
 * which restricts all SELECT queries to the authenticated user's tenant_id.
 * INSERT and UPDATE operations are unaffected by the scope.
 *
 * Usage:
 *   class Role extends Model
 *   {
 *       use ScopedByTenant;
 *   }
 */
trait ScopedByTenant
{
    public static function bootScopedByTenant(): void
    {
        static::addGlobalScope(new TenantScope);
    }
}
