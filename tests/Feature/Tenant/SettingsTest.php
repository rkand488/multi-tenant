<?php

use App\Central\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// ---------------------------------------------------------------------------
// Profile settings
// ---------------------------------------------------------------------------

it('owner can update their profile name and email', function (): void {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->tenantOwner($tenant->id)->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $this->actingAs($owner)
        ->put(route('tenant.settings.update-profile'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ])
        ->assertRedirect();

    $fresh = $owner->fresh();
    expect($fresh->name)->toBe('New Name');
    expect($fresh->email)->toBe('new@example.com');
});

it('profile update rejects duplicate email', function (): void {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->tenantOwner($tenant->id)->create(['email' => 'owner@example.com']);
    User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs($owner)
        ->put(route('tenant.settings.update-profile'), [
            'name' => 'Owner',
            'email' => 'taken@example.com',
        ])
        ->assertSessionHasErrors(['email']);
});

// ---------------------------------------------------------------------------
// Password settings
// ---------------------------------------------------------------------------

it('owner can change their password', function (): void {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->tenantOwner($tenant->id)->create(['password' => bcrypt('OldPass1!')]);

    $this->actingAs($owner)
        ->put(route('tenant.settings.update-password'), [
            'current_password' => 'OldPass1!',
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ])
        ->assertRedirect();

    expect(Hash::check('NewPass123!', $owner->fresh()->password))->toBeTrue();
});

it('rejects wrong current password', function (): void {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->tenantOwner($tenant->id)->create(['password' => bcrypt('CorrectPass1!')]);

    $this->actingAs($owner)
        ->put(route('tenant.settings.update-password'), [
            'current_password' => 'WrongPass1!',
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ])
        ->assertSessionHasErrors(['current_password']);
});

// ---------------------------------------------------------------------------
// Team settings
// ---------------------------------------------------------------------------

it('owner can update workspace name and slug', function (): void {
    $tenant = Tenant::factory()->create(['name' => 'Old Name', 'slug' => 'old-name-123']);
    $owner = User::factory()->tenantOwner($tenant->id)->create();

    $this->actingAs($owner)
        ->put(route('tenant.settings.update-team'), [
            'name' => 'New Corp',
            'slug' => 'new-corp',
        ])
        ->assertRedirect();

    $fresh = $tenant->fresh();
    expect($fresh->name)->toBe('New Corp');
    expect($fresh->slug)->toBe('new-corp');
});

it('team slug must be unique across tenants', function (): void {
    $tenantA = Tenant::factory()->create(['slug' => 'existing-slug']);
    $tenantB = Tenant::factory()->create(['slug' => 'another-slug']);
    $owner = User::factory()->tenantOwner($tenantB->id)->create();

    $this->actingAs($owner)
        ->put(route('tenant.settings.update-team'), [
            'name' => 'Another Corp',
            'slug' => 'existing-slug',
        ])
        ->assertSessionHasErrors(['slug']);
});

it('tenant user cannot update team settings', function (): void {
    $tenant = Tenant::factory()->create();
    $member = User::factory()->create(['tenant_id' => $tenant->id, 'role' => \App\Central\Enums\UserRole::TenantUser]);

    $this->actingAs($member)
        ->put(route('tenant.settings.update-team'), [
            'name' => 'Hacked',
            'slug' => 'hacked',
        ])
        ->assertForbidden();
});
