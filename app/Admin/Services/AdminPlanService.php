<?php

namespace App\Admin\Services;

use App\Central\Models\Plan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminPlanService
{
    public function listPlans(int $perPage = 15): LengthAwarePaginator
    {
        return Plan::query()->orderBy('sort_order')->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload): Plan
    {
        $payload['features'] ??= [];

        return Plan::query()->create($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(Plan $plan, array $payload): Plan
    {
        $plan->update($payload);

        return $plan->fresh();
    }

    public function delete(Plan $plan): void
    {
        $plan->delete();
    }
}
