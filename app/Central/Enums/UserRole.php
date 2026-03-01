<?php

namespace App\Central\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case TenantOwner = 'tenant_owner';
    case TenantUser = 'tenant_user';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::TenantOwner => 'Tenant Owner',
            self::TenantUser => 'Tenant User',
        };
    }

    public function isTenantScoped(): bool
    {
        return match ($this) {
            self::TenantOwner, self::TenantUser => true,
            default => false,
        };
    }
}
