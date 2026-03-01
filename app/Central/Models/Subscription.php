<?php

namespace App\Central\Models;

use App\Central\Enums\BillingInterval;
use App\Central\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $plan_id
 * @property SubscriptionStatus $status
 * @property BillingInterval $billing_interval
 * @property \Carbon\Carbon|null $trial_ends_at
 * @property \Carbon\Carbon|null $current_period_start
 * @property \Carbon\Carbon|null $current_period_end
 * @property \Carbon\Carbon|null $cancelled_at
 * @property \Carbon\Carbon|null $ends_at
 * @property string|null $stripe_subscription_id
 * @property string|null $stripe_customer_id
 * @property string|null $stripe_status
 * @property array|null $meta
 */
class Subscription extends Model
{
    use HasFactory;
    use HasUuids;

    /** @var string */
    protected $connection = 'central';

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'billing_interval',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'cancelled_at',
        'ends_at',
        'stripe_subscription_id',
        'stripe_customer_id',
        'stripe_status',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'billing_interval' => BillingInterval::class,
            'trial_ends_at' => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'cancelled_at' => 'datetime',
            'ends_at' => 'datetime',
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

    /** @return BelongsTo<Plan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /** @return HasMany<Invoice, $this> */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /** @return HasMany<Usage, $this> */
    public function usages(): HasMany
    {
        return $this->hasMany(Usage::class);
    }

    // -------------------------------------------------------------------------
    // State helpers
    // -------------------------------------------------------------------------

    public function isTrialing(): bool
    {
        return $this->status === SubscriptionStatus::Trialing
            && $this->trial_ends_at?->isFuture();
    }

    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::Active;
    }

    public function isCancelled(): bool
    {
        return $this->status === SubscriptionStatus::Cancelled;
    }

    /**
     * Whether the subscription is within a grace period after cancellation.
     * Access should still be granted until ends_at.
     */
    public function onGracePeriod(): bool
    {
        return $this->isCancelled() && $this->ends_at?->isFuture();
    }

    /**
     * Whether the tenant should currently have access (trialing, active, or grace).
     */
    public function hasAccess(): bool
    {
        return $this->status->isUsable() || $this->onGracePeriod();
    }

    // -------------------------------------------------------------------------
    // Feature gating helpers (proxied to plan)
    // -------------------------------------------------------------------------

    public function feature(string $key, mixed $default = null): mixed
    {
        return $this->plan?->feature($key, $default) ?? $default;
    }

    public function hasFeature(string $key): bool
    {
        return $this->plan?->hasFeature($key) ?? false;
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param  \Illuminate\Database\Eloquent\Builder<static>  $query */
    public function scopeUsable(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->whereIn('status', [SubscriptionStatus::Trialing->value, SubscriptionStatus::Active->value]);
    }
}
