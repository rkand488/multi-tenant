<?php

namespace App\Tenant\Services;

use App\Central\Enums\UserRole;
use App\Models\User;

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
                    'permissions' => [
                        'users.view',
                        'users.create',
                        'users.update',
                        'users.delete',
                        'roles.assign',
                        'team.settings.update',
                        'files.view',
                        'files.upload',
                        'files.delete',
                        'activity_logs.view',
                    ],
                ],
                UserRole::TenantUser->value => [
                    'label' => UserRole::TenantUser->label(),
                    'permissions' => [
                        'users.view',
                        'files.view',
                        'files.upload',
                    ],
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
