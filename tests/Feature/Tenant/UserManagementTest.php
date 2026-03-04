<?php

use App\Central\Enums\UserRole;
use App\Central\Models\Tenant;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makeTenantWithOwner(): array
{
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->tenantOwner($tenant->id)->create();

    return [$tenant, $owner];
}

// ---------------------------------------------------------------------------
// Users index
// ---------------------------------------------------------------------------

it('tenant owner can view user list', function (): void {
    [$tenant, $owner] = makeTenantWithOwner();
    User::factory()->create(['tenant_id' => $tenant->id, 'role' => UserRole::TenantUser]);

    $this->actingAs($owner)
        ->get(route('tenant.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Tenant/Users/Index'));
});

it('unauthenticated user is redirected from user list', function (): void {
    $this->get(route('tenant.users.index'))->assertRedirect(route('login'));
});

// ---------------------------------------------------------------------------
// Invite (store)
// ---------------------------------------------------------------------------

it('tenant owner can invite a new user', function (): void {
    Notification::fake();
    [$tenant, $owner] = makeTenantWithOwner();

    $this->actingAs($owner)
        ->post(route('tenant.users.store'), [
            'email' => 'new@example.com',
            'role' => UserRole::TenantUser->value,
        ])
        ->assertRedirect(route('tenant.users.index'));

    expect(Invitation::withoutGlobalScopes()->where('email', 'new@example.com')->exists())->toBeTrue();
    Notification::assertSentOnDemand(\App\Notifications\TenantInvitationNotification::class);
});

it('tenant owner cannot invite the same email twice', function (): void {
    Notification::fake();
    [$tenant, $owner] = makeTenantWithOwner();
    Invitation::factory()->create(['tenant_id' => $tenant->id, 'email' => 'dup@example.com']);

    $this->actingAs($owner)
        ->post(route('tenant.users.store'), [
            'email' => 'dup@example.com',
            'role' => UserRole::TenantUser->value,
        ])
        ->assertSessionHasErrors();
});

it('tenant user cannot invite other users', function (): void {
    [$tenant] = makeTenantWithOwner();
    $member = User::factory()->create(['tenant_id' => $tenant->id, 'role' => UserRole::TenantUser]);

    $this->actingAs($member)
        ->post(route('tenant.users.store'), [
            'email' => 'someone@example.com',
            'role' => UserRole::TenantUser->value,
        ])
        ->assertForbidden();
});

// ---------------------------------------------------------------------------
// Update role
// ---------------------------------------------------------------------------

it('tenant owner can update a user role', function (): void {
    [$tenant, $owner] = makeTenantWithOwner();
    $member = User::factory()->create(['tenant_id' => $tenant->id, 'role' => UserRole::TenantUser]);

    $this->actingAs($owner)
        ->patch(route('tenant.users.update', $member->id), ['role_id' => null])
        ->assertRedirect();
});

// ---------------------------------------------------------------------------
// Destroy
// ---------------------------------------------------------------------------

it('tenant owner can remove a team member', function (): void {
    [$tenant, $owner] = makeTenantWithOwner();
    $member = User::factory()->create(['tenant_id' => $tenant->id, 'role' => UserRole::TenantUser]);

    $this->actingAs($owner)
        ->delete(route('tenant.users.destroy', $member->id))
        ->assertRedirect();

    expect(User::on('central')->find($member->id))->toBeNull();
});

it('owner cannot remove themselves', function (): void {
    [$tenant, $owner] = makeTenantWithOwner();

    $this->actingAs($owner)
        ->delete(route('tenant.users.destroy', $owner->id))
        ->assertSessionHasErrors();
});
