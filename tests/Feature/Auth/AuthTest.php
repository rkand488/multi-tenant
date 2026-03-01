<?php

use App\Central\Enums\UserRole;
use App\Models\User;

// ---------------------------------------------------------------------------
// Register (Tenant Owner Registration)
// ---------------------------------------------------------------------------

it('rejects registration when the email is already taken', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'workspace_name' => 'Duplicate',
        'slug' => 'duplicate',
        'owner_name' => 'Bob',
        'email' => 'taken@example.com',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('rejects registration when the slug contains invalid characters', function (): void {
    $this->postJson('/api/v1/auth/register', [
        'workspace_name' => 'Bad Slug',
        'slug' => 'Bad Slug!!',
        'owner_name' => 'Carol',
        'email' => 'carol@example.com',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});

it('rejects registration with a weak password', function (): void {
    $this->postJson('/api/v1/auth/register', [
        'workspace_name' => 'Weak',
        'slug' => 'weak-co',
        'owner_name' => 'Dave',
        'email' => 'dave@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

// ---------------------------------------------------------------------------
// Login
// ---------------------------------------------------------------------------

it('issues a token on valid login', function (): void {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('secret123!'),
        'role' => UserRole::TenantOwner,
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'user@example.com',
        'password' => 'secret123!',
    ])->assertOk()
        ->assertJsonStructure(['token', 'user']);
});

it('rejects login with wrong password', function (): void {
    User::factory()->create(['email' => 'wrong@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'wrong@example.com',
        'password' => 'not-the-password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('rejects login with missing credentials', function (): void {
    $this->postJson('/api/v1/auth/login', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

// ---------------------------------------------------------------------------
// Me / Logout
// ---------------------------------------------------------------------------

it('returns the authenticated user on /me', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('user.id', $user->id);
});

it('requires authentication to access /me', function (): void {
    $this->getJson('/api/v1/auth/me')->assertUnauthorized();
});

it('logs out the current device', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/auth/logout')
        ->assertOk();

    // Flush the resolved guard state so Sanctum re-validates the token.
    auth()->forgetGuards();

    $this->withToken($token)
        ->getJson('/api/v1/auth/me')
        ->assertUnauthorized();
});

it('logs out all devices', function (): void {
    $user = User::factory()->create();
    $user->createToken('device-1');
    $user->createToken('device-2');

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/auth/logout-all')
        ->assertOk();

    expect($user->tokens()->count())->toBe(0);
});
