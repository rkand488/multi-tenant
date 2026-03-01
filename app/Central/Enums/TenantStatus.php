<?php

namespace App\Central\Enums;

enum TenantStatus: string
{
    case Provisioning = 'provisioning';
    case Active = 'active';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Provisioning => 'Provisioning',
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isAccessible(): bool
    {
        return $this === self::Active;
    }

    public function color(): string
    {
        return match ($this) {
            self::Provisioning => 'yellow',
            self::Active => 'green',
            self::Suspended => 'red',
            self::Cancelled => 'gray',
        };
    }
}
