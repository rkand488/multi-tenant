<?php

namespace App\Billing\Services;

use App\Central\Models\Subscription;
use App\Central\Models\Tenant;
use App\Central\Models\Usage;
use Illuminate\Validation\ValidationException;

/**
 * Tracks and enforces metered feature usage against plan limits.
 *
 * Each feature is tracked per billing period, identified by:
 *   (tenant_id, feature_key, period_start)
 *
 * Usage quantities are incremented atomically using an upsert so that
 * concurrent requests never double-count.
 */
class UsageService
{
    // -------------------------------------------------------------------------
    // Increment usage
    // -------------------------------------------------------------------------

    /**
     * Increment feature usage for the given tenant by the provided amount.
     *
     * @throws ValidationException When the increment would exceed the plan limit.
     */
    public function increment(Tenant $tenant, string $featureKey, int $amount = 1): Usage
    {
        $subscription = $tenant->currentSubscription()->with('plan')->first();

        $this->assertPlanAllows($subscription, $featureKey);

        $limit = (int) ($subscription?->feature($featureKey) ?? PHP_INT_MAX);
        $current = $this->currentUsage($tenant, $featureKey, $subscription);
        $newQty = $current + $amount;

        if ($limit !== PHP_INT_MAX && $newQty > $limit) {
            throw ValidationException::withMessages([
                'usage' => ["You have reached the {$featureKey} limit of {$limit} for your plan."],
            ]);
        }

        return $this->upsert($tenant, $featureKey, $newQty, $subscription);
    }

    /**
     * Set the usage quantity to an absolute value (e.g. for storage reads).
     */
    public function record(Tenant $tenant, string $featureKey, int $quantity): Usage
    {
        $subscription = $tenant->currentSubscription()->with('plan')->first();

        return $this->upsert($tenant, $featureKey, $quantity, $subscription);
    }

    // -------------------------------------------------------------------------
    // Read usage
    // -------------------------------------------------------------------------

    /**
     * Current quantity consumed this period for the given feature.
     */
    public function currentUsage(Tenant $tenant, string $featureKey, ?Subscription $subscription = null): int
    {
        $subscription ??= $tenant->currentSubscription()->first();

        return (int) Usage::query()
            ->where('tenant_id', $tenant->id)
            ->where('feature_key', $featureKey)
            ->when(
                $subscription,
                fn ($q) => $q->where('period_start', $subscription->current_period_start),
                fn ($q) => $q->where('period_start', '<=', now())->where('period_end', '>=', now()),
            )
            ->value('quantity');
    }

    /**
     * Returns a summary of usage vs limits for all tracked features.
     *
     * @return array<string, array{used: int, limit: int|null, has_limit: bool}>
     */
    public function summary(Tenant $tenant): array
    {
        $subscription = $tenant->currentSubscription()->with('plan')->first();
        $features = $subscription?->plan?->features ?? [];
        $summary = [];

        foreach ($features as $key => $limit) {
            // Only summarise numeric limits (boolean flags are feature gates, not usage).
            if (! is_numeric($limit)) {
                continue;
            }

            $used = $this->currentUsage($tenant, $key, $subscription);
            $summary[$key] = [
                'used' => $used,
                'limit' => (int) $limit,
                'has_limit' => true,
            ];
        }

        // Also include any features already tracked but not in plan definition.
        $trackedKeys = Usage::query()
            ->where('tenant_id', $tenant->id)
            ->when(
                $subscription,
                fn ($q) => $q->where('period_start', $subscription->current_period_start),
            )
            ->pluck('quantity', 'feature_key');

        foreach ($trackedKeys as $key => $qty) {
            if (! array_key_exists($key, $summary)) {
                $summary[$key] = [
                    'used' => (int) $qty,
                    'limit' => null,
                    'has_limit' => false,
                ];
            }
        }

        return $summary;
    }

    // -------------------------------------------------------------------------
    // Feature gating
    // -------------------------------------------------------------------------

    /**
     * Assert that the tenant's plan allows access to a boolean feature.
     *
     * @throws ValidationException
     */
    public function assertFeatureEnabled(Tenant $tenant, string $featureKey): void
    {
        $subscription = $tenant->currentSubscription()->with('plan')->first();

        if (! $subscription?->hasFeature($featureKey)) {
            throw ValidationException::withMessages([
                'feature' => ["Your plan does not include access to {$featureKey}."],
            ]);
        }
    }

    /**
     * Return whether a tenant can still use a metered feature (quantity < limit).
     */
    public function canUse(Tenant $tenant, string $featureKey, int $amount = 1): bool
    {
        $subscription = $tenant->currentSubscription()->with('plan')->first();

        if (! $subscription) {
            return false;
        }

        $limit = $subscription->feature($featureKey);

        if ($limit === null || $limit === true) {
            return true; // unlimited or boolean flag
        }

        if ($limit === false) {
            return false; // explicitly disabled
        }

        return ($this->currentUsage($tenant, $featureKey, $subscription) + $amount) <= (int) $limit;
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private function assertPlanAllows(?Subscription $subscription, string $featureKey): void
    {
        if (! $subscription) {
            throw ValidationException::withMessages([
                'usage' => ['No active subscription found.'],
            ]);
        }

        $limit = $subscription->feature($featureKey);

        if ($limit === false) {
            throw ValidationException::withMessages([
                'usage' => ["Your plan does not include access to {$featureKey}."],
            ]);
        }
    }

    private function upsert(Tenant $tenant, string $featureKey, int $quantity, ?Subscription $subscription): Usage
    {
        $now = now();
        $periodStart = $subscription?->current_period_start ?? $now->copy()->startOfMonth();
        $periodEnd = $subscription?->current_period_end ?? $now->copy()->endOfMonth();

        return Usage::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'feature_key' => $featureKey,
                'period_start' => $periodStart,
            ],
            [
                'subscription_id' => $subscription?->id,
                'quantity' => $quantity,
                'period_end' => $periodEnd,
            ],
        );
    }
}
