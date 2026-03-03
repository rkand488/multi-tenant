<?php

namespace App\Http\Controllers\Api\Admin;

use App\Admin\Services\AdminUsageStatisticsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexUsageStatisticsRequest;
use Illuminate\Http\JsonResponse;

/**
 * @tags Admin System
 */
class UsageStatisticController extends Controller
{
    public function __construct(
        private readonly AdminUsageStatisticsService $usageStatisticsService,
    ) {}

    /**
     * Get usage statistics.
     *
     * Returns system-wide feature usage statistics. Super admin only.
     *
     * @response array{summary: object, top_tenants: array}
     */
    public function index(IndexUsageStatisticsRequest $request): JsonResponse
    {
        $days = (int) $request->integer('days', 30);

        $summary = $this->usageStatisticsService->featureSummary(
            perPage: (int) $request->integer('per_page', 15),
            days: $days,
            tenantId: $request->string('tenant_id')->toString() ?: null,
            feature: $request->string('feature')->toString() ?: null,
        );

        return response()->json([
            'summary' => $summary,
            'top_tenants' => $this->usageStatisticsService->topTenants($days),
        ]);
    }
}
