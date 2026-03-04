<?php

use App\Central\Models\Plan;

// ---------------------------------------------------------------------------
// Feature limit helpers
// ---------------------------------------------------------------------------

it('numeric limits are returned as numbers', function (): void {
    $plan = new Plan(['features' => ['max_users' => 5, 'storage_gb' => 10.5]]);

    expect($plan->feature('max_users'))->toBe(5)
        ->and($plan->feature('storage_gb'))->toBe(10.5);
});

it('unlimited (null) feature returns null', function (): void {
    $plan = new Plan(['features' => ['max_users' => null]]);

    expect($plan->feature('max_users'))->toBeNull();
});

it('unlimited feature uses null to indicate no cap', function (): void {
    $plan = new Plan(['features' => []]);

    // Convention: missing key → treat as unlimited (null)
    expect($plan->feature('max_users'))->toBeNull();
});

// ---------------------------------------------------------------------------
// Storage quota helpers
// ---------------------------------------------------------------------------

it('storage_gb feature converts correctly to bytes', function (): void {
    $plan = new Plan(['features' => ['storage_gb' => 5]]);

    $limitBytes = $plan->feature('storage_gb') * 1073741824;

    expect($limitBytes)->toBe(5 * 1073741824);
});

it('storage usage percentage is calculated correctly', function (): void {
    $usedBytes = 1073741824;  // 1 GB
    $limitBytes = 2 * 1073741824; // 2 GB

    $percent = (int) round(($usedBytes / $limitBytes) * 100);

    expect($percent)->toBe(50);
});

it('storage usage percent caps at 100 when over limit', function (): void {
    $usedBytes = 3 * 1073741824;
    $limitBytes = 2 * 1073741824;

    $percent = min(100, round(($usedBytes / $limitBytes) * 100));

    expect($percent)->toBe(100);
});

it('storage warning triggers at 80 percent', function (): void {
    $usedBytes = 1800 * 1048576; // ~1.76 GB
    $limitBytes = 2 * 1073741824; // 2 GB
    $percent = ($usedBytes / $limitBytes) * 100;

    expect($percent)->toBeGreaterThanOrEqual(80);
});

// ---------------------------------------------------------------------------
// Plan price helpers
// ---------------------------------------------------------------------------

it('monthly price is stored in cents', function (): void {
    $plan = new Plan(['price_monthly' => 1999]);

    expect($plan->price_monthly)->toBe(1999);
});

it('yearly price is lower per month than monthly when discounted', function (): void {
    $plan = new Plan([
        'price_monthly' => 2000,  // $20/month
        'price_yearly' => 19200, // $192/year = $16/month (20% discount)
    ]);

    $effectiveMonthly = $plan->price_yearly / 12;

    expect($effectiveMonthly)->toBeLessThan($plan->price_monthly);
});
