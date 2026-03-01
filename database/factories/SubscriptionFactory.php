<?php

namespace Database\Factories;

use App\Central\Enums\BillingInterval;
use App\Central\Enums\SubscriptionStatus;
use App\Central\Models\Plan;
use App\Central\Models\Subscription;
use App\Central\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /** @var class-string<Subscription> */
    protected $model = Subscription::class;

    public function definition(): array
    {
        $start = now()->startOfMonth();

        return [
            'tenant_id' => Str::uuid()->toString(),
            'plan_id' => Plan::factory(),
            'status' => SubscriptionStatus::Active,
            'billing_interval' => BillingInterval::Monthly,
            'trial_ends_at' => null,
            'current_period_start' => $start,
            'current_period_end' => $start->copy()->addMonth(),
            'cancelled_at' => null,
            'ends_at' => null,
        ];
    }

    public function trialing(): static
    {
        return $this->state(fn () => [
            'status' => SubscriptionStatus::Trialing,
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now()->subDay(),
            'ends_at' => now()->addDays(7), // grace period
        ]);
    }

    public function pastDue(): static
    {
        return $this->state(fn () => ['status' => SubscriptionStatus::PastDue]);
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn () => ['tenant_id' => $tenant->id]);
    }
}
