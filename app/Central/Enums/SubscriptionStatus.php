<?php

namespace App\Central\Enums;

enum SubscriptionStatus: string
{
    case Trialing = 'trialing';
    case Active = 'active';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Trialing => 'Trialing',
            self::Active => 'Active',
            self::PastDue => 'Past Due',
            self::Cancelled => 'Cancelled',
            self::Suspended => 'Suspended',
        };
    }

    /**
     * Whether the tenant can use the product with this subscription status.
     */
    public function isUsable(): bool
    {
        return match ($this) {
            self::Trialing, self::Active => true,
            default => false,
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Trialing => 'blue',
            self::Active => 'green',
            self::PastDue => 'yellow',
            self::Cancelled => 'gray',
            self::Suspended => 'red',
        };
    }
}
