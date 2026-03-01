<?php

namespace App\Billing\Services;

use App\Central\Enums\BillingInterval;
use App\Central\Enums\SubscriptionStatus;
use App\Central\Models\Plan;
use App\Central\Models\Subscription;
use App\Central\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Manages the full subscription lifecycle for a tenant.
 *
 * All database writes use the `central` connection and are wrapped in
 * transactions so that half-created records can never occur.
 *
 * Stripe integration points are clearly marked with @stripe comments —
 * they are the only external calls that need to be added when wiring up
 * the payment gateway.
 */
class SubscriptionService
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {}

    // -------------------------------------------------------------------------
    // Subscribe / Trial
    // -------------------------------------------------------------------------

    /**
     * Start a new subscription (trialing or immediately active).
     *
     * @param  array{billing_interval?: string, stripe_customer_id?: string}  $options
     *
     * @throws ValidationException
     */
    public function subscribe(Tenant $tenant, Plan $plan, array $options = []): Subscription
    {
        if ($this->getActiveSubscription($tenant)) {
            throw ValidationException::withMessages([
                'subscription' => ['This workspace already has an active subscription.'],
            ]);
        }

        $interval = BillingInterval::from($options['billing_interval'] ?? BillingInterval::Monthly->value);

        return DB::connection('central')->transaction(function () use ($tenant, $plan, $interval, $options): Subscription {
            $isTrial = $plan->trial_days > 0;
            $now = now();

            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => $isTrial ? SubscriptionStatus::Trialing : SubscriptionStatus::Active,
                'billing_interval' => $interval,
                'trial_ends_at' => $isTrial ? $now->copy()->addDays($plan->trial_days) : null,
                'current_period_start' => $now,
                'current_period_end' => $now->copy()->addMonths($interval->months()),
                'stripe_customer_id' => $options['stripe_customer_id'] ?? null,
            ]);

            // @stripe — Create Stripe subscription here and store stripe_subscription_id.

            // Issue the first invoice if the plan has a cost and is not trialing.
            if (! $isTrial && $plan->priceFor($interval) > 0) {
                $this->invoiceService->issueForSubscription($subscription);
            }

            return $subscription;
        });
    }

    // -------------------------------------------------------------------------
    // Plan changes
    // -------------------------------------------------------------------------

    /**
     * Upgrade or downgrade an existing subscription to a new plan.
     * The change takes effect immediately (proration is handled by Stripe in production).
     */
    public function changePlan(Subscription $subscription, Plan $plan, BillingInterval $interval): Subscription
    {
        // @stripe — Update the Stripe subscription here (swap plan + interval).

        $subscription->update([
            'plan_id' => $plan->id,
            'billing_interval' => $interval,
            'status' => SubscriptionStatus::Active,
            'trial_ends_at' => null,
        ]);

        return $subscription->fresh(['plan']);
    }

    // -------------------------------------------------------------------------
    // Cancellation
    // -------------------------------------------------------------------------

    /**
     * Cancel a subscription immediately or at period end.
     *
     * @param  bool  $atPeriodEnd  If true the subscription stays Active until current_period_end.
     */
    public function cancel(Subscription $subscription, bool $atPeriodEnd = true): Subscription
    {
        // @stripe — Cancel the Stripe subscription here.

        $subscription->update([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
            'ends_at' => $atPeriodEnd ? $subscription->current_period_end : now(),
        ]);

        return $subscription->fresh();
    }

    /**
     * Resume a cancelled subscription that is still within its grace period.
     */
    public function resume(Subscription $subscription): Subscription
    {
        if (! $subscription->onGracePeriod()) {
            throw ValidationException::withMessages([
                'subscription' => ['This subscription cannot be resumed — it is past its grace period.'],
            ]);
        }

        // @stripe — Resume the Stripe subscription here.

        $subscription->update([
            'status' => SubscriptionStatus::Active,
            'cancelled_at' => null,
            'ends_at' => null,
        ]);

        return $subscription->fresh();
    }

    // -------------------------------------------------------------------------
    // Trial conversion
    // -------------------------------------------------------------------------

    /**
     * Convert a trialing subscription to Active (called after payment method added).
     */
    public function activateFromTrial(Subscription $subscription): Subscription
    {
        if (! $subscription->isTrialing()) {
            throw ValidationException::withMessages([
                'subscription' => ['This subscription is not currently trialing.'],
            ]);
        }

        // @stripe — Confirm and charge here.

        $subscription->update([
            'status' => SubscriptionStatus::Active,
            'trial_ends_at' => null,
        ]);

        if ($subscription->plan->priceFor($subscription->billing_interval) > 0) {
            $this->invoiceService->issueForSubscription($subscription->fresh(['plan']));
        }

        return $subscription->fresh();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function getActiveSubscription(Tenant $tenant): ?Subscription
    {
        return $tenant->currentSubscription()->with('plan')->first();
    }
}
