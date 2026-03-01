<?php

namespace App\Billing\Services;

use App\Central\Enums\InvoiceStatus;
use App\Central\Models\Invoice;
use App\Central\Models\Subscription;
use App\Central\Models\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Creates, marks paid, and voids invoices.
 *
 * Invoice numbers are auto-incremented on the central connection using a
 * row-level lock to prevent duplicates under concurrent requests.
 */
class InvoiceService
{
    /**
     * Issue an invoice for a subscription renewal period.
     */
    public function issueForSubscription(Subscription $subscription): Invoice
    {
        $plan = $subscription->plan;
        $amount = $plan->priceFor($subscription->billing_interval);

        return DB::connection('central')->transaction(function () use ($subscription, $amount): Invoice {
            return Invoice::create([
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'number' => $this->nextNumber(),
                'status' => InvoiceStatus::Open,
                'amount_due' => $amount,
                'amount_paid' => 0,
                'currency' => 'usd',
                'billing_interval' => $subscription->billing_interval,
                'period_start' => $subscription->current_period_start,
                'period_end' => $subscription->current_period_end,
                'due_date' => now()->addDays(7)->toDateString(),
            ]);
        });
    }

    /**
     * Mark an invoice as paid (called from a Stripe webhook handler).
     *
     * @param  array{stripe_payment_intent_id?: string}  $meta
     */
    public function markPaid(Invoice $invoice, array $meta = []): Invoice
    {
        $invoice->update([
            'status' => InvoiceStatus::Paid,
            'amount_paid' => $invoice->amount_due,
            'paid_at' => now(),
            'stripe_payment_intent_id' => $meta['stripe_payment_intent_id'] ?? null,
        ]);

        return $invoice->fresh();
    }

    /**
     * Void an open invoice (e.g. after plan downgrade or manual credit).
     */
    public function void(Invoice $invoice): Invoice
    {
        if ($invoice->isPaid()) {
            throw new \LogicException('Cannot void a paid invoice.');
        }

        $invoice->update(['status' => InvoiceStatus::Void]);

        return $invoice->fresh();
    }

    /**
     * Return paginated invoices for a tenant, newest first.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<Invoice>
     */
    public function listForTenant(Tenant $tenant, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $tenant->invoices()
            ->latest()
            ->paginate($perPage);
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /**
     * Generate the next sequential invoice number, safely under a shared lock.
     */
    private function nextNumber(): string
    {
        $last = Invoice::query()
            ->lockForUpdate()
            ->latest('created_at')
            ->value('number');

        if ($last === null) {
            return 'INV-0001';
        }

        $seq = (int) ltrim(str_replace('INV-', '', $last), '0');

        return 'INV-'.str_pad((string) ($seq + 1), 4, '0', STR_PAD_LEFT);
    }
}
