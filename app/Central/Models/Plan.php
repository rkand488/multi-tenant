<?php

namespace App\Central\Models;

use App\Central\Enums\BillingInterval;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a pricing plan (e.g. Starter, Growth, Enterprise).
 *
 * Feature limits are stored as a flexible JSON blob:
 *   { "max_users": 5, "max_projects": 20, "api_access": true }
 *
 * Use Plan::feature() to read a limit safely with a default.
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $price_monthly Cents
 * @property int $price_yearly Cents
 * @property int $trial_days
 * @property array $features
 * @property bool $is_active
 * @property int $sort_order
 * @property string|null $stripe_monthly_price_id
 * @property string|null $stripe_yearly_price_id
 */
class Plan extends Model
{
    use HasFactory;
    use HasUuids;

    /** @var string */
    protected $connection = 'central';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'trial_days',
        'features',
        'is_active',
        'sort_order',
        'stripe_monthly_price_id',
        'stripe_yearly_price_id',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'integer',
            'price_yearly' => 'integer',
            'trial_days' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /** @return HasMany<Subscription, $this> */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Read a feature limit from the JSON features blob.
     */
    public function feature(string $key, mixed $default = null): mixed
    {
        return data_get($this->features ?? [], $key, $default);
    }

    /**
     * Whether the plan has a specific feature (boolean flags).
     */
    public function hasFeature(string $key): bool
    {
        return (bool) $this->feature($key, false);
    }

    /**
     * Price in cents for the given billing interval.
     */
    public function priceFor(BillingInterval $interval): int
    {
        return match ($interval) {
            BillingInterval::Monthly => $this->price_monthly,
            BillingInterval::Yearly => $this->price_yearly,
        };
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param  \Illuminate\Database\Eloquent\Builder<static>  $query */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order');
    }
}
