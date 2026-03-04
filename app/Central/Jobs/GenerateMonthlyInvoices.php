<?php

namespace App\Central\Jobs;

use App\Billing\Services\InvoiceService;
use App\Central\Enums\SubscriptionStatus;
use App\Central\Models\Subscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Runs monthly via the scheduler to generate invoices for all active subscriptions
 * that have entered a new billing period.
 *
 * Queue: billing
 * Schedule: monthly
 */
class GenerateMonthlyInvoices implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 300;

    public function __construct()
    {
        $this->onQueue('billing');
    }

    public function handle(InvoiceService $invoiceService): void
    {
        Subscription::on('central')
            ->whereIn('status', [SubscriptionStatus::Active->value])
            ->whereNotNull('current_period_end')
            ->where('current_period_end', '<=', now())
            ->with(['tenant', 'plan'])
            ->each(function (Subscription $subscription) use ($invoiceService): void {
                $invoiceService->issueForSubscription($subscription);

                // Advance the billing period.
                $subscription->update([
                    'current_period_start' => $subscription->current_period_end,
                    'current_period_end' => $subscription->current_period_end->copy()
                        ->addMonths($subscription->billing_interval->months()),
                ]);
            });
    }
}
