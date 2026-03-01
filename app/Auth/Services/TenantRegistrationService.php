<?php

namespace App\Auth\Services;

use App\Central\Enums\TenantStatus;
use App\Central\Enums\UserRole;
use App\Central\Models\Domain;
use App\Central\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Handles tenant owner registration: creates the Tenant record in the central
 * database and the owner User record.
 *
 * All writes are wrapped in a transaction so the two records are always
 * consistent.
 */
class TenantRegistrationService
{
    /**
     * @param  array{name: string, slug: string, email: string, password: string, owner_name: string}  $data
     */
    public function register(array $data): array
    {
        $slug = Str::slug($data['slug']);

        if (Tenant::query()->where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'slug' => ['This workspace name is already taken.'],
            ]);
        }

        return DB::connection('central')->transaction(function () use ($data, $slug): array {
            $tenant = Tenant::create([
                'name' => $data['name'],
                'slug' => $slug,
                'owner_email' => $data['email'],
                'status' => TenantStatus::Provisioning,
            ]);

            // Create the auto-generated primary subdomain.
            Domain::create([
                'tenant_id' => $tenant->id,
                'domain' => $slug.'.'.config('tenancy.domain'),
                'is_primary' => true,
                'is_verified' => true,
                'verified_at' => now(),
            ]);

            // The owner lives in the tenant's own database. We switch to it
            // temporarily to create the user, then return to central.
            // During registration the tenant DB does not exist yet, so the
            // owner record is stored in the central DB (tenant_id scoped).
            $owner = User::create([
                'name' => $data['owner_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => UserRole::TenantOwner,
                'tenant_id' => $tenant->id,
            ]);

            // Transition tenant to Active immediately (provisioning logic can
            // be extended here to queue DB creation, etc.).
            $tenant->update(['status' => TenantStatus::Active]);

            return ['tenant' => $tenant, 'user' => $owner];
        });
    }
}
