<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Services\AdminUsageStatisticsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexUsageStatisticsRequest;
use Illuminate\Http\JsonResponse;

class UsageStatisticController extends Controller
{
    public function __construct(
        private readonly AdminUsageStatisticsService $usageStatisticsService,
    ) {}

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
