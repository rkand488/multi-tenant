<?php

namespace App\Auth\Services;

use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Handles credential-based authentication for all user roles.
 * Returns a Sanctum plain-text token on success.
 */
class AuthenticationService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * Authenticate a user and issue a Sanctum API token.
     *
     * When the request arrives from a tenant subdomain or configured custom
     * domain the TenantContext will already be populated by the
     * IdentifyTenantIfPresent middleware.  In that case we additionally
     * enforce that the user belongs to that specific tenant, preventing a
     * user from one workspace logging in via another tenant's domain.
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

        // If the login originates from a tenant domain, the user must belong
        // to that specific tenant.  A super-admin (no tenant_id) or a user
        // from a different tenant will be rejected with a generic error to
        // avoid leaking information about account existence.
        $tenant = $this->tenantContext->getOrNull();

        if ($tenant !== null && $user->tenant_id !== $tenant->id) {
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
