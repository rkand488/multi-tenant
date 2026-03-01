<?php

namespace App\Admin\Services;

use App\Central\Models\Usage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AdminUsageStatisticsService
{
    public function featureSummary(
        int $perPage = 15,
        int $days = 30,
        ?string $tenantId = null,
        ?string $feature = null,
    ): LengthAwarePaginator {
        return Usage::query()
            ->selectRaw('feature_key, COUNT(DISTINCT tenant_id) as tenant_count, SUM(quantity) as total_quantity, AVG(quantity) as average_quantity')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($feature, fn ($query) => $query->where('feature_key', $feature))
            ->where('period_start', '>=', now()->subDays($days)->startOfDay())
            ->groupBy('feature_key')
            ->orderByDesc('total_quantity')
            ->paginate($perPage);
    }

    /**
     * @return Collection<int, object>
     */
    public function topTenants(int $days = 30, int $limit = 10): Collection
    {
        return Usage::query()
            ->selectRaw('tenant_id, SUM(quantity) as total_quantity')
            ->where('period_start', '>=', now()->subDays($days)->startOfDay())
            ->groupBy('tenant_id')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
    }
}
