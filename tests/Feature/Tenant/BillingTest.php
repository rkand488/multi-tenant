<?php

use App\Billing\Services\SubscriptionService;
use App\Central\Enums\BillingInterval;
use App\Central\Enums\SubscriptionStatus;
use App\Central\Models\Plan;
use App\Central\Models\Subscription;
use App\Central\Models\Tenant;
use App\Models\User;

// ---------------------------------------------------------------------------
// Billing page
// ---------------------------------------------------------------------------

it('owner can view the billing page', function (): void {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->tenantOwner($tenant->id)->create();

    $this->actingAs($owner)
        ->get(route('tenant.billing.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Tenant/Billing/Subscription'));
});

// ---------------------------------------------------------------------------
// Cancel subscription
// ---------------------------------------------------------------------------

it('owner can cancel an active subscription', function (): void {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->tenantOwner($tenant->id)->create();
    $plan = Plan::factory()->create(['is_active' => true]);

    $subscription = Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'plan_id' => $plan->id,
        'status' => SubscriptionStatus::Active,
        'billing_interval' => BillingInterval::Monthly,
        'current_period_start' => now(),
        'current_period_end' => now()->addMonth(),
    ]);

    $this->actingAs($owner)
        ->post(route('tenant.billing.cancel'))
        ->assertRedirect();

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Cancelled);
});

it('returns error when cancelling with no subscription', function (): void {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->tenantOwner($tenant->id)->create();

    $this->actingAs($owner)
        ->post(route('tenant.billing.cancel'))
        ->assertSessionHasErrors([]);
});

it('tenant user cannot cancel subscription', function (): void {
    $tenant = Tenant::factory()->create();
    $member = User::factory()->create(['tenant_id' => $tenant->id, 'role' => \App\Central\Enums\UserRole::TenantUser]);

    $this->actingAs($member)
        ->post(route('tenant.billing.cancel'))
        ->assertForbidden();
});

// ---------------------------------------------------------------------------
// Upgrade subscription
// ---------------------------------------------------------------------------

it('owner can subscribe to a plan', function (): void {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->tenantOwner($tenant->id)->create();
    $plan = Plan::factory()->create(['is_active' => true, 'price_monthly' => 0]);

    $this->actingAs($owner)
        ->post(route('tenant.billing.upgrade'), [
            'plan_id' => $plan->id,
            'billing_interval' => 'monthly',
        ])
        ->assertRedirect();

    expect(Subscription::on('central')->where('tenant_id', $tenant->id)->exists())->toBeTrue();
});

it('owner can change to a different plan', function (): void {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->tenantOwner($tenant->id)->create();
    $planA = Plan::factory()->create(['is_active' => true, 'price_monthly' => 0]);
    $planB = Plan::factory()->create(['is_active' => true, 'price_monthly' => 0]);

    // Start on Plan A
    app(SubscriptionService::class)->subscribe($tenant, $planA, ['billing_interval' => 'monthly']);

    $this->actingAs($owner)
        ->post(route('tenant.billing.upgrade'), [
            'plan_id' => $planB->id,
            'billing_interval' => 'monthly',
        ])
        ->assertRedirect();

    expect(Subscription::on('central')
        ->where('tenant_id', $tenant->id)
        ->where('plan_id', $planB->id)
        ->exists()
    )->toBeTrue();
});
