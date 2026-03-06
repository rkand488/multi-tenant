<?php

use App\Central\Enums\InvoiceStatus;
use App\Central\Enums\SubscriptionStatus;
use App\Central\Models\Invoice;
use App\Central\Models\Plan;
use App\Central\Models\Subscription;
use App\Central\Models\Tenant;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Build a Stripe-Signature header value for the given payload and secret.
 */
function stripeSignature(string $rawBody, string $secret, ?int $timestamp = null): string
{
    $timestamp ??= time();
    $signed = $timestamp.'.'.$rawBody;
    $signature = hash_hmac('sha256', $signed, $secret);

    return "t={$timestamp},v1={$signature}";
}

/**
 * POST to the webhook endpoint, optionally with a Stripe-Signature header.
 */
function postWebhook(array $payload, ?string $signature = null): \Illuminate\Testing\TestResponse
{
    $headers = ['Accept' => 'application/json'];

    if ($signature !== null) {
        $headers['Stripe-Signature'] = $signature;
    }

    return test()->postJson('/api/v1/webhooks/stripe', $payload, $headers);
}

// ---------------------------------------------------------------------------
// Signature verification
// ---------------------------------------------------------------------------

it('accepts the webhook when no webhook secret is configured', function (): void {
    config(['services.stripe.webhook_secret' => null]);

    postWebhook(['type' => 'unknown.event', 'data' => ['object' => []]])
        ->assertOk();
});

it('rejects the webhook when an invalid signature is sent', function (): void {
    config(['services.stripe.webhook_secret' => 'whsec_test_secret']);

    postWebhook(
        ['type' => 'unknown.event', 'data' => ['object' => []]],
        't=9999999,v1=invalidsignature',
    )->assertStatus(401);
});

it('accepts the webhook when a valid signature is sent', function (): void {
    $secret = 'whsec_test_secret';
    config(['services.stripe.webhook_secret' => $secret]);

    $payload = ['type' => 'unknown.event', 'data' => ['object' => []]];
    $rawBody = json_encode($payload);
    $signature = stripeSignature($rawBody, $secret);

    // Use `call()` to control the exact raw body so the signature matches.
    test()->call('POST', '/api/v1/webhooks/stripe', [], [], [], [
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $rawBody)->assertOk();
});

// ---------------------------------------------------------------------------
// invoice.paid
// ---------------------------------------------------------------------------

it('marks an open invoice as paid on invoice.paid', function (): void {
    config(['services.stripe.webhook_secret' => null]);

    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->forTenant($tenant)->create([
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_test_123',
        'status' => SubscriptionStatus::Active,
    ]);
    $invoice = Invoice::factory()->create([
        'tenant_id' => $tenant->id,
        'subscription_id' => $subscription->id,
        'status' => InvoiceStatus::Open,
    ]);

    postWebhook([
        'type' => 'invoice.paid',
        'data' => ['object' => [
            'subscription' => 'sub_test_123',
            'payment_intent' => 'pi_test_456',
        ]],
    ])->assertOk();

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Paid)
        ->and($invoice->fresh()->stripe_payment_intent_id)->toBe('pi_test_456');
});

it('restores a past_due subscription to active on invoice.paid', function (): void {
    config(['services.stripe.webhook_secret' => null]);

    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->forTenant($tenant)->pastDue()->create([
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_pastdue_789',
    ]);

    postWebhook([
        'type' => 'invoice.paid',
        'data' => ['object' => [
            'subscription' => 'sub_pastdue_789',
            'payment_intent' => 'pi_test_abc',
        ]],
    ])->assertOk();

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Active);
});

it('ignores invoice.paid when subscription is not found', function (): void {
    config(['services.stripe.webhook_secret' => null]);

    postWebhook([
        'type' => 'invoice.paid',
        'data' => ['object' => [
            'subscription' => 'sub_does_not_exist',
        ]],
    ])->assertOk();
});

// ---------------------------------------------------------------------------
// invoice.payment_failed
// ---------------------------------------------------------------------------

it('marks subscription as past_due on invoice.payment_failed', function (): void {
    config(['services.stripe.webhook_secret' => null]);

    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->forTenant($tenant)->create([
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_fail_001',
        'status' => SubscriptionStatus::Active,
    ]);

    postWebhook([
        'type' => 'invoice.payment_failed',
        'data' => ['object' => ['subscription' => 'sub_fail_001']],
    ])->assertOk();

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::PastDue);
});

it('ignores invoice.payment_failed when subscription is not found', function (): void {
    config(['services.stripe.webhook_secret' => null]);

    postWebhook([
        'type' => 'invoice.payment_failed',
        'data' => ['object' => ['subscription' => 'sub_ghost']],
    ])->assertOk();
});

// ---------------------------------------------------------------------------
// customer.subscription.deleted
// ---------------------------------------------------------------------------

it('cancels subscription on customer.subscription.deleted', function (): void {
    config(['services.stripe.webhook_secret' => null]);

    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->forTenant($tenant)->create([
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_del_001',
        'status' => SubscriptionStatus::Active,
    ]);

    postWebhook([
        'type' => 'customer.subscription.deleted',
        'data' => ['object' => [
            'id' => 'sub_del_001',
            'current_period_end' => now()->addDays(5)->timestamp,
        ]],
    ])->assertOk();

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Cancelled)
        ->and($subscription->fresh()->cancelled_at)->not->toBeNull();
});

it('ignores customer.subscription.deleted when subscription is not found', function (): void {
    config(['services.stripe.webhook_secret' => null]);

    postWebhook([
        'type' => 'customer.subscription.deleted',
        'data' => ['object' => ['id' => 'sub_ghost_del']],
    ])->assertOk();
});

// ---------------------------------------------------------------------------
// customer.subscription.updated
// ---------------------------------------------------------------------------

it('syncs subscription status on customer.subscription.updated', function (): void {
    config(['services.stripe.webhook_secret' => null]);

    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->forTenant($tenant)->create([
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_upd_001',
        'status' => SubscriptionStatus::Active,
    ]);

    postWebhook([
        'type' => 'customer.subscription.updated',
        'data' => ['object' => [
            'id' => 'sub_upd_001',
            'status' => 'past_due',
        ]],
    ])->assertOk();

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::PastDue)
        ->and($subscription->fresh()->stripe_status)->toBe('past_due');
});

it('handles unrecognised Stripe subscription status gracefully', function (): void {
    config(['services.stripe.webhook_secret' => null]);

    $tenant = Tenant::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->forTenant($tenant)->create([
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_upd_002',
        'status' => SubscriptionStatus::Active,
    ]);

    postWebhook([
        'type' => 'customer.subscription.updated',
        'data' => ['object' => [
            'id' => 'sub_upd_002',
            'status' => 'some_future_stripe_status',
        ]],
    ])->assertOk();

    // Status should remain unchanged.
    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Active);
});

// ---------------------------------------------------------------------------
// Unknown events
// ---------------------------------------------------------------------------

it('returns 200 for unknown event types', function (): void {
    config(['services.stripe.webhook_secret' => null]);

    postWebhook([
        'type' => 'charge.succeeded',
        'data' => ['object' => []],
    ])->assertOk();
});
