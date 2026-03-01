<?php

use App\Central\Enums\BillingInterval;
use App\Central\Enums\SubscriptionStatus;
use App\Central\Models\Plan;
use App\Central\Models\Subscription;
use App\Central\Models\Tenant;
use App\Http\Middleware\EnsureTenantIsActive;
use App\Http\Middleware\IdentifyTenant;
use App\Models\User;
use App\Tenancy\TenantContext;

/**
 * Returns a pair of [User, Tenant] already wired to the TenantContext so that
 * tenant middleware can be bypassed in every test.
 *
 * @return array{0: User, 1: Tenant}
 */
function makeTenantUser(): array
{
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    app(TenantContext::class)->set($tenant);

    return [$user, $tenant];
}

// ---------------------------------------------------------------------------
// Show current subscription
// ---------------------------------------------------------------------------

it('returns null when tenant has no subscription', function (): void {
    [$user] = makeTenantUser();

    $this->actingAs($user, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/api/v1/billing/subscription')
        ->assertNotFound()
        ->assertJsonPath('data', null);
});

it('returns the active subscription', function (): void {
    [$user, $tenant] = makeTenantUser();
    $plan = Plan::factory()->create(['is_active' => true]);
    Subscription::factory()->forTenant($tenant)->create(['plan_id' => $plan->id, 'status' => SubscriptionStatus::Active]);

    $this->actingAs($user, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/api/v1/billing/subscription')
        ->assertOk()
        ->assertJsonPath('data.status', SubscriptionStatus::Active->value);
});

// ---------------------------------------------------------------------------
// Subscribe
// ---------------------------------------------------------------------------

it('subscribes a tenant to a plan', function (): void {
    [$user, $tenant] = makeTenantUser();
    $plan = Plan::factory()->create(['is_active' => true, 'trial_days' => 0]);

    $this->actingAs($user, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->postJson('/api/v1/billing/subscription', [
            'plan_id' => $plan->id,
            'billing_interval' => BillingInterval::Monthly->value,
        ])->assertCreated()
        ->assertJsonPath('data.status', SubscriptionStatus::Active->value);

    expect(Subscription::query()->where('tenant_id', $tenant->id)->exists())->toBeTrue();
});

it('subscribes a tenant with a trial period', function (): void {
    [$user, $tenant] = makeTenantUser();
    $plan = Plan::factory()->create(['is_active' => true, 'trial_days' => 14]);

    $this->actingAs($user, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->postJson('/api/v1/billing/subscription', [
            'plan_id' => $plan->id,
            'billing_interval' => BillingInterval::Monthly->value,
        ])->assertCreated()
        ->assertJsonPath('data.status', SubscriptionStatus::Trialing->value);
});

it('rejects subscribing when tenant already has an active subscription', function (): void {
    [$user, $tenant] = makeTenantUser();
    $plan = Plan::factory()->create(['is_active' => true, 'trial_days' => 0]);
    Subscription::factory()->forTenant($tenant)->create(['plan_id' => $plan->id, 'status' => SubscriptionStatus::Active]);

    $this->actingAs($user, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->postJson('/api/v1/billing/subscription', [
            'plan_id' => $plan->id,
            'billing_interval' => BillingInterval::Monthly->value,
        ])->assertUnprocessable();
});

it('rejects subscribing without required fields', function (): void {
    [$user] = makeTenantUser();

    $this->actingAs($user, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->postJson('/api/v1/billing/subscription', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['plan_id', 'billing_interval']);
});

// ---------------------------------------------------------------------------
// Change plan
// ---------------------------------------------------------------------------

it('changes the plan on an active subscription', function (): void {
    [$user, $tenant] = makeTenantUser();
    $planA = Plan::factory()->create(['is_active' => true, 'trial_days' => 0]);
    $planB = Plan::factory()->create(['is_active' => true, 'trial_days' => 0]);
    Subscription::factory()->forTenant($tenant)->create(['plan_id' => $planA->id, 'status' => SubscriptionStatus::Active]);

    $this->actingAs($user, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->patchJson('/api/v1/billing/subscription', [
            'plan_id' => $planB->id,
            'billing_interval' => BillingInterval::Yearly->value,
        ])->assertOk()
        ->assertJsonPath('data.plan.id', $planB->id);
});

// ---------------------------------------------------------------------------
// Cancel
// ---------------------------------------------------------------------------

it('cancels a subscription at period end', function (): void {
    [$user, $tenant] = makeTenantUser();
    $plan = Plan::factory()->create(['is_active' => true, 'trial_days' => 0]);
    Subscription::factory()->forTenant($tenant)->create(['plan_id' => $plan->id, 'status' => SubscriptionStatus::Active]);

    $this->actingAs($user, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->deleteJson('/api/v1/billing/subscription')
        ->assertOk()
        ->assertJsonPath('data.status', SubscriptionStatus::Cancelled->value);
});

// ---------------------------------------------------------------------------
// Resume
// ---------------------------------------------------------------------------

it('resumes a cancelled subscription within the grace period', function (): void {
    [$user, $tenant] = makeTenantUser();
    $plan = Plan::factory()->create(['is_active' => true, 'trial_days' => 0]);
    Subscription::factory()->forTenant($tenant)->cancelled()->create(['plan_id' => $plan->id]);

    $this->actingAs($user, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->postJson('/api/v1/billing/subscription/resume')
        ->assertOk()
        ->assertJsonPath('data.status', SubscriptionStatus::Active->value);
});

// ---------------------------------------------------------------------------
// RequireActiveSubscription middleware
// ---------------------------------------------------------------------------

it('returns 402 when accessing invoices without a subscription', function (): void {
    [$user] = makeTenantUser();

    $this->actingAs($user, 'sanctum')
        ->withoutMiddleware([IdentifyTenant::class, EnsureTenantIsActive::class])
        ->getJson('/api/v1/billing/invoices')
        ->assertPaymentRequired();
});
