<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

// ---------------------------------------------------------------------------
// Forgot password — show form
// ---------------------------------------------------------------------------

it('renders the forgot password page', function (): void {
    $this->get(route('password.request'))->assertOk();
});

// ---------------------------------------------------------------------------
// Send reset link
// ---------------------------------------------------------------------------

it('sends a reset link to a known email without errors', function (): void {
    Notification::fake();

    $user = User::factory()->create(['email' => 'reset@example.com']);

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertSessionHasNoErrors();
});

it('does not reveal whether the account exists on reset link', function (): void {
    $this->post(route('password.email'), ['email' => 'nobody@example.com'])
        ->assertRedirect();
});

it('rejects a missing email on the reset link form', function (): void {
    $this->post(route('password.email'), [])->assertSessionHasErrors(['email']);
});

// ---------------------------------------------------------------------------
// Reset password — show form
// ---------------------------------------------------------------------------

it('renders the reset password page with a token', function (): void {
    $this->get(route('password.reset', ['token' => 'sometoken', 'email' => 'user@example.com']))
        ->assertOk();
});

// ---------------------------------------------------------------------------
// Reset password — process
// ---------------------------------------------------------------------------

it('resets the password with a valid token', function (): void {
    $user = User::factory()->create(['email' => 'change@example.com']);

    $token = Password::broker('users')->createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewPassword1!',
        'password_confirmation' => 'NewPassword1!',
    ])->assertRedirect(route('login'));

    expect(Hash::check('NewPassword1!', $user->fresh()->password))->toBeTrue();
});

it('rejects an invalid reset token', function (): void {
    User::factory()->create(['email' => 'bad@example.com']);

    $this->post(route('password.update'), [
        'token' => 'invalid-token',
        'email' => 'bad@example.com',
        'password' => 'NewPassword1!',
        'password_confirmation' => 'NewPassword1!',
    ])->assertSessionHasErrors(['email']);
});

it('rejects a weak new password on reset', function (): void {
    $user = User::factory()->create(['email' => 'weak@example.com']);
    $token = Password::broker('users')->createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertSessionHasErrors(['password']);
});
