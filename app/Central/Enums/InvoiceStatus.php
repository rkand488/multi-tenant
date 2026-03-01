<?php

namespace App\Central\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Paid = 'paid';
    case Void = 'void';
    case Uncollectible = 'uncollectible';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Open => 'Open',
            self::Paid => 'Paid',
            self::Void => 'Void',
            self::Uncollectible => 'Uncollectible',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Open => 'yellow',
            self::Paid => 'green',
            self::Void => 'gray',
            self::Uncollectible => 'red',
        };
    }
}
