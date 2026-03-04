<?php

namespace App\Tenancy\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global Eloquent scope that automatically filters queries by the
 * authenticated user's tenant_id.
 *
 * Only activates when a user is logged in AND has a tenant_id set,
 * so super-admin queries (no tenant_id) are never restricted.
 *
 * Apply via the ScopedByTenant trait on any tenant-scoped model.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = auth()->user()?->tenant_id;

        if ($tenantId !== null) {
            $builder->where($model->getTable().'.tenant_id', $tenantId);
        }
    }
}
