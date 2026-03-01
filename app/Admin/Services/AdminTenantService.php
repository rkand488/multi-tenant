<?php

namespace App\Admin\Services;

use App\Central\Enums\TenantStatus;
use App\Central\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminTenantService
{
    public function listTenants(int $perPage = 15, ?string $search = null, ?TenantStatus $status = null): LengthAwarePaginator
    {
        return Tenant::query()
            ->with(['primaryDomain', 'currentSubscription.plan'])
            ->when($search, function ($query, $term): void {
                $query->where(function ($innerQuery) use ($term): void {
                    $innerQuery
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%")
                        ->orWhere('owner_email', 'like', "%{$term}%");
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage);
    }

    public function suspend(Tenant $tenant): Tenant
    {
        $tenant->update(['status' => TenantStatus::Suspended]);

        return $tenant->fresh(['primaryDomain', 'currentSubscription.plan']);
    }

    public function updateStatus(Tenant $tenant, TenantStatus $status): Tenant
    {
        if ($status === TenantStatus::Suspended) {
            return $this->suspend($tenant);
        }

        $tenant->update(['status' => $status]);

        return $tenant->fresh(['primaryDomain', 'currentSubscription.plan']);
    }
}
