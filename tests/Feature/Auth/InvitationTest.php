<?php

use App\Central\Enums\UserRole;
use App\Models\Invitation;
use App\Models\User;
use App\Tenancy\TenantContext;

// ---------------------------------------------------------------------------
// Show invitation (public)
// ---------------------------------------------------------------------------

it('returns invitation details for a valid pending token', function (): void {
    $invitation = Invitation::factory()->create();

    $this->getJson("/api/v1/invitations/{$invitation->token}")
        ->assertOk()
        ->assertJsonPath('invitation.email', $invitation->email);
});

it('returns 422 for an expired invitation token', function (): void {
    $invitation = Invitation::factory()->expired()->create();

    $this->getJson("/api/v1/invitations/{$invitation->token}")
        ->assertUnprocessable();
});

it('returns 422 for an unknown token', function (): void {
    $this->getJson('/api/v1/invitations/nonexistent-token-000000000000000000000000')
        ->assertUnprocessable();
});

// ---------------------------------------------------------------------------
// Accept invitation
// ---------------------------------------------------------------------------

it('allows accepting a valid invitation and creates a user', function (): void {
    $invitation = Invitation::factory()->create(['email' => 'new@example.com']);

    $this->postJson('/api/v1/invitations/accept', [
        'token' => $invitation->token,
        'name' => 'New User',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ])->assertCreated()
        ->assertJsonStructure(['token', 'user']);

    expect(User::query()->where('email', 'new@example.com')->exists())->toBeTrue();
    expect($invitation->fresh()->isAccepted())->toBeTrue();
});

it('rejects accepting an expired invitation', function (): void {
    $invitation = Invitation::factory()->expired()->create();

    $this->postJson('/api/v1/invitations/accept', [
        'token' => $invitation->token,
        'name' => 'Late User',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['token']);
});

it('rejects accepting an already-accepted invitation', function (): void {
    $invitation = Invitation::factory()->accepted()->create();

    $this->postJson('/api/v1/invitations/accept', [
        'token' => $invitation->token,
        'name' => 'Repeat User',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['token']);
});

// ---------------------------------------------------------------------------
// Store invitation (tenant owner only)
// ---------------------------------------------------------------------------

it('allows a tenant owner to invite a user', function (): void {
    $tenantId = (string) \Illuminate\Support\Str::uuid();
    $owner = User::factory()->create(['role' => UserRole::TenantOwner, 'tenant_id' => $tenantId]);

    // Seed the TenantContext directly and bypass the tenant middleware stack
    // so we don't need a real MySQL central database in tests.
    $mockTenant = new \App\Central\Models\Tenant;
    $mockTenant->id = $tenantId;
    app(TenantContext::class)->set($mockTenant);

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([\App\Http\Middleware\IdentifyTenant::class, \App\Http\Middleware\EnsureTenantIsActive::class])
        ->postJson('/api/v1/invitations', [
            'email' => 'invite@example.com',
            'role' => 'tenant_user',
        ])->assertCreated()
        ->assertJsonPath('invitation.email', 'invite@example.com');
});

it('forbids a tenant user from sending invitations', function (): void {
    $tenantId = (string) \Illuminate\Support\Str::uuid();
    $user = User::factory()->create(['role' => UserRole::TenantUser, 'tenant_id' => $tenantId]);

    $mockTenant = new \App\Central\Models\Tenant;
    $mockTenant->id = $tenantId;
    app(TenantContext::class)->set($mockTenant);

    $this->actingAs($user, 'sanctum')
        ->withoutMiddleware([\App\Http\Middleware\IdentifyTenant::class, \App\Http\Middleware\EnsureTenantIsActive::class])
        ->postJson('/api/v1/invitations', [
            'email' => 'denied@example.com',
            'role' => 'tenant_user',
        ])->assertForbidden();
});

it('forbids guests from sending invitations', function (): void {
    $this->withoutMiddleware([\App\Http\Middleware\IdentifyTenant::class, \App\Http\Middleware\EnsureTenantIsActive::class])
        ->postJson('/api/v1/invitations', [
            'email' => 'ghost@example.com',
            'role' => 'tenant_user',
        ])->assertUnauthorized();
});

// ---------------------------------------------------------------------------
// Destroy invitation
// ---------------------------------------------------------------------------

it('allows owner to revoke their own invitation', function (): void {
    $tenantId = (string) \Illuminate\Support\Str::uuid();
    $owner = User::factory()->create(['role' => UserRole::TenantOwner, 'tenant_id' => $tenantId]);
    $invitation = Invitation::factory()->create(['tenant_id' => $tenantId]);

    $mockTenant = new \App\Central\Models\Tenant;
    $mockTenant->id = $tenantId;
    app(TenantContext::class)->set($mockTenant);

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([\App\Http\Middleware\IdentifyTenant::class, \App\Http\Middleware\EnsureTenantIsActive::class])
        ->deleteJson("/api/v1/invitations/{$invitation->id}")
        ->assertOk();

    expect(Invitation::find($invitation->id))->toBeNull();
});

it('returns 404 for another tenant\'s invitation (TenantScope hides it)', function (): void {
    $owner = User::factory()->create(['role' => UserRole::TenantOwner, 'tenant_id' => 'tenant-a']);
    $otherInvite = Invitation::factory()->create(['tenant_id' => 'tenant-b']);

    $mockTenant = new \App\Central\Models\Tenant;
    $mockTenant->id = 'tenant-a';
    app(TenantContext::class)->set($mockTenant);

    $this->actingAs($owner, 'sanctum')
        ->withoutMiddleware([\App\Http\Middleware\IdentifyTenant::class, \App\Http\Middleware\EnsureTenantIsActive::class])
        ->deleteJson("/api/v1/invitations/{$otherInvite->id}")
        ->assertNotFound();
});
