<?php

namespace App\Tenant\Services;

use App\Central\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class TenantUserService
{
    public function listUsers(string $tenantId, int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return User::query()
            ->where('tenant_id', $tenantId)
            ->when($search, function ($query, $term): void {
                $query->where(function ($innerQuery) use ($term): void {
                    $innerQuery
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function createUser(string $tenantId, array $payload): User
    {
        return User::query()->create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
            'role' => UserRole::from($payload['role']),
            'tenant_id' => $tenantId,
        ]);
    }

    public function findUser(string $tenantId, int|string $userId): User
    {
        return User::query()
            ->where('tenant_id', $tenantId)
            ->whereKey($userId)
            ->firstOrFail();
    }

    public function updateUser(User $user, array $payload): User
    {
        $user->update($payload);

        return $user->fresh();
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
    }
}
