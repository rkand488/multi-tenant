<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\IndexActivityLogRequest;
use App\Tenancy\TenantContext;
use App\Tenant\Services\TenantActivityLogService;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly TenantActivityLogService $activityLogService,
    ) {}

    public function index(IndexActivityLogRequest $request): JsonResponse
    {
        if (! $request->user()?->isTenantOwner()) {
            return response()->json(['message' => 'Forbidden. Tenant owner role required.'], 403);
        }

        $tenant = $this->tenantContext->get();
        $logs = $this->activityLogService->listForTenant(
            tenant: $tenant,
            perPage: (int) $request->integer('per_page', 15),
            action: $request->string('action')->toString() ?: null,
            userId: $request->filled('user_id') ? (int) $request->integer('user_id') : null,
        );

        return response()->json($logs);
    }

    public function show(string $activity_log): JsonResponse
    {
        if (! request()->user()?->isTenantOwner()) {
            return response()->json(['message' => 'Forbidden. Tenant owner role required.'], 403);
        }

        $tenant = $this->tenantContext->get();
        $log = $this->activityLogService->findForTenant($tenant, $activity_log);

        return response()->json(['data' => $log]);
    }
}
