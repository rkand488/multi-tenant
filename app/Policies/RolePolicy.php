<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

/**
 * Controls who can manage roles within a tenant workspace.
 *
 * Super admins can do anything (via before()).
 * Tenant owners can create, view, update, and delete custom roles.
 * Tenant users are read-only.
 * System roles cannot be deleted.
 */
class RolePolicy
{
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isTenantOwner() || $user->isTenantUser();
    }

    public function view(User $user, Role $role): bool
    {
        return $user->tenant_id === $role->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->isTenantOwner();
    }

    public function update(User $user, Role $role): bool
    {
        return $user->isTenantOwner()
            && $user->tenant_id === $role->tenant_id;
    }

    /**
     * System roles cannot be deleted by anyone (super admins bypass via before()).
     */
    public function delete(User $user, Role $role): bool
    {
        if (! empty($role->is_system)) {
            return false;
        }

        return $user->isTenantOwner()
            && $user->tenant_id === $role->tenant_id;
    }
}
