<?php

namespace App\Billing\Services;

use App\Central\Models\Plan;

/**
 * Read-only helpers for listing and retrieving plans.
 * Plan creation and management is done via Artisan / admin panel.
 */
class PlanService
{
    /**
     * All active plans ordered by sort_order.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Plan>
     */
    public function listActive(): \Illuminate\Database\Eloquent\Collection
    {
        return Plan::query()->active()->get();
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findBySlug(string $slug): Plan
    {
        return Plan::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();
    }

    /**
     * Compare two plans by their features.
     *
     * @return array<string, array{from: mixed, to: mixed, changed: bool}>
     */
    public function compareFeatures(Plan $from, Plan $to): array
    {
        $keys = array_unique(array_merge(
            array_keys($from->features ?? []),
            array_keys($to->features ?? []),
        ));

        $diff = [];

        foreach ($keys as $key) {
            $a = data_get($from->features, $key);
            $b = data_get($to->features, $key);

            $diff[$key] = [
                'from' => $a,
                'to' => $b,
                'changed' => $a !== $b,
            ];
        }

        return $diff;
    }
}
