<?php

namespace App\Tenant\Support;

class TenantPermissionCatalog
{
    /** @return list<string> */
    public static function webAssignable(): array
    {
        return [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'settings.view',
            'settings.update',
            'billing.view',
            'billing.manage',
            'files.view',
            'files.upload',
            'files.delete',
        ];
    }

    /** @return list<string> */
    public static function tenantOwnerDefault(): array
    {
        return [
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
        ];
    }

    /** @return list<string> */
    public static function tenantUserDefault(): array
    {
        return [
            'users.view',
            'files.view',
            'files.upload',
        ];
    }
}
