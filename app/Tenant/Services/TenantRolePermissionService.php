<?php

namespace App\Tenant\Services;

use App\Central\Enums\UserRole;
use App\Models\User;
use App\Tenant\Support\TenantPermissionCatalog;

class TenantRolePermissionService
{
    /**
     * @return array<string, mixed>
     */
    public function getRolePermissionMatrix(): array
    {
        return [
            'roles' => [
                UserRole::TenantOwner->value => [
                    'label' => UserRole::TenantOwner->label(),
                    'permissions' => TenantPermissionCatalog::tenantOwnerDefault(),
                ],
                UserRole::TenantUser->value => [
                    'label' => UserRole::TenantUser->label(),
                    'permissions' => TenantPermissionCatalog::tenantUserDefault(),
                ],
            ],
        ];
    }

    public function assignRole(User $user, UserRole $role): User
    {
        $user->update(['role' => $role]);

        return $user->fresh();
    }
}
