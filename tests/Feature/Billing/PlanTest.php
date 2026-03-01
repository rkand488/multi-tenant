<?php

use App\Central\Models\Plan;

// ---------------------------------------------------------------------------
// List plans
// ---------------------------------------------------------------------------

it('returns only active plans', function (): void {
    Plan::factory()->count(2)->create(['is_active' => true]);
    Plan::factory()->create(['is_active' => false]);

    $this->getJson('/api/v1/plans')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('returns an empty list when no plans are active', function (): void {
    Plan::factory()->create(['is_active' => false]);

    $this->getJson('/api/v1/plans')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('returns plans ordered by sort_order', function (): void {
    Plan::factory()->create(['name' => 'Enterprise', 'sort_order' => 3, 'is_active' => true]);
    Plan::factory()->create(['name' => 'Starter',    'sort_order' => 1, 'is_active' => true]);
    Plan::factory()->create(['name' => 'Pro',        'sort_order' => 2, 'is_active' => true]);

    $response = $this->getJson('/api/v1/plans')->assertOk();

    expect($response->json('data.0.name'))->toBe('Starter')
        ->and($response->json('data.1.name'))->toBe('Pro')
        ->and($response->json('data.2.name'))->toBe('Enterprise');
});

// ---------------------------------------------------------------------------
// Show plan
// ---------------------------------------------------------------------------

it('returns a single plan', function (): void {
    $plan = Plan::factory()->create(['is_active' => true]);

    $this->getJson("/api/v1/plans/{$plan->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $plan->id);
});

it('returns 404 for an unknown plan', function (): void {
    $this->getJson('/api/v1/plans/00000000-0000-0000-0000-000000000000')
        ->assertNotFound();
});

// ---------------------------------------------------------------------------
// Plan features
// ---------------------------------------------------------------------------

it('includes the features json on a plan', function (): void {
    $plan = Plan::factory()
        ->withFeature('max_users', 10)
        ->withFeature('api_access', true)
        ->create(['is_active' => true]);

    $response = $this->getJson("/api/v1/plans/{$plan->id}")->assertOk();

    expect($response->json('data.features.max_users'))->toBe(10)
        ->and($response->json('data.features.api_access'))->toBeTrue();
});

it('correctly exposes a free plan', function (): void {
    $plan = Plan::factory()->free()->create();

    expect($plan->priceFor(\App\Central\Enums\BillingInterval::Monthly))->toBe(0)
        ->and($plan->priceFor(\App\Central\Enums\BillingInterval::Yearly))->toBe(0);
});
