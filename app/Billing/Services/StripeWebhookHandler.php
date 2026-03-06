<?php

namespace App\Billing\Services;

use App\Central\Enums\SubscriptionStatus;
use App\Central\Models\Invoice;
use App\Central\Models\Subscription;
use Illuminate\Support\Facades\Log;

/**
 * Processes inbound Stripe webhook payloads.
 *
 * Each public method maps to a Stripe event type and is responsible for
 * updating the local database to reflect the remote Stripe state.  All
 * methods are idempotent — calling them multiple times with the same payload
 * produces the same result.
 *
 * Stripe SDK integration is intentionally left as @stripe stubs; this class
 * can be wired to real Stripe events without changing the caller.
 */
class StripeWebhookHandler
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {}

    /**
     * Dispatch a raw Stripe event payload to the appropriate handler method.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): void
    {
        $type = $payload['type'] ?? '';
        $data = $payload['data']['object'] ?? [];

        match ($type) {
            'invoice.paid' => $this->handleInvoicePaid($data),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($data),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($data),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($data),
            default => Log::info('Unhandled Stripe webhook event', ['type' => $type]),
        };
    }

    // -------------------------------------------------------------------------
    // Event handlers
    // -------------------------------------------------------------------------

    /**
     * Mark the corresponding local invoice as paid when Stripe confirms payment.
     *
     * Stripe sends this event when a subscription invoice is successfully paid.
     * We locate the matching open Invoice by stripe_subscription_id and mark it
     * paid, recording the payment_intent ID for reconciliation.
     *
     * @param  array<string, mixed>  $data  Stripe invoice object
     */
    public function handleInvoicePaid(array $data): void
    {
        $stripeSubscriptionId = $data['subscription'] ?? null;

        if (! $stripeSubscriptionId) {
            return;
        }

        $subscription = Subscription::query()
            ->where('stripe_subscription_id', $stripeSubscriptionId)
            ->first();

        if (! $subscription) {
            Log::warning('Stripe webhook: subscription not found for invoice.paid', [
                'stripe_subscription_id' => $stripeSubscriptionId,
            ]);

            return;
        }

        // If the subscription was past_due, restore it to active.
        if ($subscription->status === SubscriptionStatus::PastDue) {
            $subscription->update(['status' => SubscriptionStatus::Active]);
        }

        // Find the most recent open invoice for this subscription.
        $invoice = Invoice::query()
            ->where('subscription_id', $subscription->id)
            ->where('status', \App\Central\Enums\InvoiceStatus::Open)
            ->latest()
            ->first();

        if (! $invoice) {
            return;
        }

        $this->invoiceService->markPaid($invoice, [
            'stripe_payment_intent_id' => $data['payment_intent'] ?? null,
        ]);
    }

    /**
     * Mark the subscription as past_due when a Stripe invoice payment fails.
     *
     * @param  array<string, mixed>  $data  Stripe invoice object
     */
    public function handleInvoicePaymentFailed(array $data): void
    {
        $stripeSubscriptionId = $data['subscription'] ?? null;

        if (! $stripeSubscriptionId) {
            return;
        }

        $subscription = Subscription::query()
            ->where('stripe_subscription_id', $stripeSubscriptionId)
            ->first();

        if (! $subscription) {
            Log::warning('Stripe webhook: subscription not found for invoice.payment_failed', [
                'stripe_subscription_id' => $stripeSubscriptionId,
            ]);

            return;
        }

        $subscription->update(['status' => SubscriptionStatus::PastDue]);
    }

    /**
     * Cancel the local subscription when Stripe deletes it.
     *
     * @param  array<string, mixed>  $data  Stripe subscription object
     */
    public function handleSubscriptionDeleted(array $data): void
    {
        $stripeSubscriptionId = $data['id'] ?? null;

        if (! $stripeSubscriptionId) {
            return;
        }

        $subscription = Subscription::query()
            ->where('stripe_subscription_id', $stripeSubscriptionId)
            ->first();

        if (! $subscription) {
            return;
        }

        $subscription->update([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
            'ends_at' => isset($data['current_period_end'])
                ? \Carbon\Carbon::createFromTimestamp($data['current_period_end'])
                : now(),
        ]);
    }

    /**
     * Sync the local subscription status when Stripe updates it.
     *
     * @param  array<string, mixed>  $data  Stripe subscription object
     */
    public function handleSubscriptionUpdated(array $data): void
    {
        $stripeSubscriptionId = $data['id'] ?? null;
        $stripeStatus = $data['status'] ?? null;

        if (! $stripeSubscriptionId || ! $stripeStatus) {
            return;
        }

        $subscription = Subscription::query()
            ->where('stripe_subscription_id', $stripeSubscriptionId)
            ->first();

        if (! $subscription) {
            return;
        }

        // @stripe Map Stripe subscription statuses to our internal enum.
        $statusMap = [
            'trialing' => SubscriptionStatus::Trialing,
            'active' => SubscriptionStatus::Active,
            'past_due' => SubscriptionStatus::PastDue,
            'canceled' => SubscriptionStatus::Cancelled,
            'cancelled' => SubscriptionStatus::Cancelled,
            'unpaid' => SubscriptionStatus::PastDue,
        ];

        $newStatus = $statusMap[$stripeStatus] ?? null;

        if ($newStatus === null) {
            Log::info('Stripe webhook: unrecognised subscription status', ['stripe_status' => $stripeStatus]);

            return;
        }

        $subscription->update([
            'status' => $newStatus,
            'stripe_status' => $stripeStatus,
        ]);
    }
}
