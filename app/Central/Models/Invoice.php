<?php

namespace App\Central\Models;

use App\Central\Enums\BillingInterval;
use App\Central\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string|null $subscription_id
 * @property string $number
 * @property InvoiceStatus $status
 * @property int $amount_due Cents
 * @property int $amount_paid Cents
 * @property string $currency
 * @property BillingInterval|null $billing_interval
 * @property \Carbon\Carbon|null $period_start
 * @property \Carbon\Carbon|null $period_end
 * @property \Carbon\Carbon|null $paid_at
 * @property \Carbon\CarbonImmutable|null $due_date
 * @property string|null $stripe_invoice_id
 * @property string|null $stripe_payment_intent_id
 * @property array|null $meta
 */
class Invoice extends Model
{
    use HasFactory;
    use HasUuids;

    /** @var string */
    protected $connection = 'central';

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'number',
        'status',
        'amount_due',
        'amount_paid',
        'currency',
        'billing_interval',
        'period_start',
        'period_end',
        'paid_at',
        'due_date',
        'stripe_invoice_id',
        'stripe_payment_intent_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'billing_interval' => BillingInterval::class,
            'amount_due' => 'integer',
            'amount_paid' => 'integer',
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'paid_at' => 'datetime',
            'due_date' => 'date',
            'meta' => 'array',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return BelongsTo<Subscription, $this> */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::Paid;
    }

    /**
     * Outstanding amount in cents.
     */
    public function amountOutstanding(): int
    {
        return max(0, $this->amount_due - $this->amount_paid);
    }

    /**
     * Format amount in dollars (or equivalent) for display.
     */
    public function formattedTotal(): string
    {
        return '$'.number_format($this->amount_due / 100, 2);
    }
}
