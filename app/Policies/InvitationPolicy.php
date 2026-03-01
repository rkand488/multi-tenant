<?php

namespace App\Policies;

use App\Models\Invitation;
use App\Models\User;
use App\Tenancy\TenantContext;

/**
 * Authorises invitation-related actions.
 *
 * Tenant owners may manage invitations within their own workspace.
 * Super admins have full access.
 */
class InvitationPolicy
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * Super admins bypass all policy checks.
     */
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Only tenant owners can send invitations.
     */
    public function create(User $user): bool
    {
        return $user->isTenantOwner()
            && $this->userBelongsToCurrentTenant($user);
    }

    /**
     * Only the inviting tenant owner (or a super admin) can revoke invitations.
     */
    public function delete(User $user, Invitation $invitation): bool
    {
        return $user->isTenantOwner()
            && $invitation->tenant_id === $user->tenant_id;
    }

    private function userBelongsToCurrentTenant(User $user): bool
    {
        $tenant = $this->tenantContext->getOrNull();

        return $tenant !== null && $user->tenant_id === $tenant->id;
    }
}
