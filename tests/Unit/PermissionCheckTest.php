<?php

use App\Central\Models\Plan;
use App\Models\Role;

// ---------------------------------------------------------------------------
// Role::permissions array
// ---------------------------------------------------------------------------

it('permission present in the array is considered granted', function (): void {
    $role = new Role(['permissions' => ['users.view', 'users.create']]);

    expect(in_array('users.view', $role->permissions ?? [], true))->toBeTrue();
});

it('permission absent from the array is not granted', function (): void {
    $role = new Role(['permissions' => ['users.view']]);

    expect(in_array('users.delete', $role->permissions ?? [], true))->toBeFalse();
});

it('roles with null permissions array grant nothing', function (): void {
    $role = new Role(['permissions' => null]);

    expect(in_array('users.view', $role->permissions ?? [], true))->toBeFalse();
});

it('permissions are cast to an array', function (): void {
    $role = new Role(['permissions' => ['users.view', 'billing.view']]);

    expect($role->permissions)->toBeArray()->toHaveCount(2);
});

// ---------------------------------------------------------------------------
// Plan::feature() and Plan::hasFeature()
// ---------------------------------------------------------------------------

it('plan feature returns the stored value', function (): void {
    $plan = new Plan(['features' => ['max_users' => 10]]);

    expect($plan->feature('max_users'))->toBe(10);
});

it('plan feature returns the default when key is missing', function (): void {
    $plan = new Plan(['features' => []]);

    expect($plan->feature('max_users', 999))->toBe(999);
});

it('plan feature returns null default when key missing and no default given', function (): void {
    $plan = new Plan(['features' => []]);

    expect($plan->feature('max_users'))->toBeNull();
});

it('hasFeature returns true for a truthy boolean flag', function (): void {
    $plan = new Plan(['features' => ['api_access' => true]]);

    expect($plan->hasFeature('api_access'))->toBeTrue();
});

it('hasFeature returns false for a falsy boolean flag', function (): void {
    $plan = new Plan(['features' => ['api_access' => false]]);

    expect($plan->hasFeature('api_access'))->toBeFalse();
});

it('hasFeature returns false when key is absent', function (): void {
    $plan = new Plan(['features' => []]);

    expect($plan->hasFeature('audit_logs'))->toBeFalse();
});
