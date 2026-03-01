<?php

namespace App\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Handles credential-based authentication for all user roles.
 * Returns a Sanctum plain-text token on success.
 */
class AuthenticationService
{
    /**
     * Authenticate a user and issue a Sanctum API token.
     *
     * @param  array{email: string, password: string, device_name?: string}  $credentials
     *
     * @throws ValidationException
     */
    public function authenticate(array $credentials): array
    {
        $user = User::query()
            ->where('email', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $deviceName = $credentials['device_name'] ?? 'api';

        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Revoke the current bearer token (logout from current device).
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Revoke all tokens for the user (logout from all devices).
     */
    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }
}
