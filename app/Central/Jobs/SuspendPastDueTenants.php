<?php

namespace App\Central\Jobs;

use App\Central\Enums\SubscriptionStatus;
use App\Central\Enums\TenantStatus;
use App\Central\Models\Subscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Runs daily via the scheduler.
 * Suspends tenants whose subscription has been past_due beyond the grace period
 * (default: 7 days defined by tenancy.grace_period_days config).
 *
 * Queue: billing
 * Schedule: daily
 */
class SuspendPastDueTenants implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct()
    {
        $this->onQueue('billing');
    }

    public function handle(): void
    {
        $gracePeriodDays = (int) config('tenancy.grace_period_days', 7);
        $cutoffDate = now()->subDays($gracePeriodDays);

        Subscription::on('central')
            ->where('status', SubscriptionStatus::PastDue->value)
            ->where('updated_at', '<=', $cutoffDate)
            ->with('tenant')
            ->each(function (Subscription $subscription): void {
                $subscription->tenant?->update([
                    'status' => TenantStatus::Suspended,
                ]);

                $subscription->update(['status' => SubscriptionStatus::Cancelled]);
            });
    }
}
