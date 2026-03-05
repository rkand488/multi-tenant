<?php

namespace App\Tenancy\Scopes;

use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global Eloquent scope that automatically filters queries by the
 * current tenant resolved from the TenantContext singleton.
 *
 * Only activates when a tenant has been set by the IdentifyTenant or
 * IdentifyTenantIfPresent middleware.  Central/super-admin requests
 * (no tenant context) are never restricted.
 *
 * Apply via the ScopedByTenant trait on any tenant-scoped model.
 *
 * NOTE: This scope deliberately reads from TenantContext instead of
 * auth()->user() to avoid an infinite recursion when the User model
 * itself uses ScopedByTenant (the session guard tries to load the User
 * which triggers the scope which calls auth()->user() again).
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenant = app(TenantContext::class)->getOrNull();

        if ($tenant !== null) {
            $builder->where($model->getTable().'.tenant_id', $tenant->id);
        }
    }
}
