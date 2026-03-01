<?php

namespace App\Policies;

use App\Models\User;

/**
 * Controls visibility and mutation of User records.
 *
 * Super admins can manage everyone.
 * Tenant owners can manage users in their own workspace.
 * Tenant users can only view/update their own profile.
 */
class UserPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Tenant owners can view any member of their workspace.
     */
    public function view(User $viewer, User $target): bool
    {
        if ($viewer->id === $target->id) {
            return true;
        }

        return $viewer->isTenantOwner()
            && $viewer->tenant_id === $target->tenant_id;
    }

    /**
     * Listing users is allowed for tenant owners within their workspace.
     */
    public function viewAny(User $user): bool
    {
        return $user->isTenantOwner();
    }

    /**
     * Users may update their own profile; owners may update workspace members.
     */
    public function update(User $viewer, User $target): bool
    {
        if ($viewer->id === $target->id) {
            return true;
        }

        return $viewer->isTenantOwner()
            && $viewer->tenant_id === $target->tenant_id;
    }

    /**
     * Only tenant owners may remove users from their workspace.
     */
    public function delete(User $viewer, User $target): bool
    {
        if ($viewer->id === $target->id) {
            return false; // Cannot delete yourself.
        }

        return $viewer->isTenantOwner()
            && $viewer->tenant_id === $target->tenant_id;
    }
}
