<?php

namespace App\Tenant\Services;

use App\Central\Models\ActivityLog;
use App\Central\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TenantActivityLogService
{
    public function listForTenant(
        Tenant $tenant,
        int $perPage = 15,
        ?string $action = null,
        ?int $userId = null,
    ): LengthAwarePaginator {
        return ActivityLog::query()
            ->where('tenant_id', $tenant->id)
            ->when($action, fn ($query) => $query->where('action', $action))
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->latest()
            ->paginate($perPage);
    }

    public function findForTenant(Tenant $tenant, int|string $logId): ActivityLog
    {
        return ActivityLog::query()
            ->where('tenant_id', $tenant->id)
            ->whereKey($logId)
            ->firstOrFail();
    }

    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function record(
        Tenant $tenant,
        string $action,
        ?int $userId = null,
        ?string $subjectType = null,
        int|string|null $subjectId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?array $meta = null,
    ): ActivityLog {
        return ActivityLog::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId !== null ? (string) $subjectId : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'meta' => $meta,
        ]);
    }
}
