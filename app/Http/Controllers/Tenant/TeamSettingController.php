<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateTeamSettingRequest;
use App\Tenancy\TenantContext;
use App\Tenant\Services\TenantActivityLogService;
use App\Tenant\Services\TenantTeamSettingsService;
use Illuminate\Http\JsonResponse;

class TeamSettingController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly TenantTeamSettingsService $teamSettingsService,
        private readonly TenantActivityLogService $activityLogService,
    ) {}

    public function index(): JsonResponse
    {
        if (! request()->user()?->isTenantOwner()) {
            return response()->json(['message' => 'Forbidden. Tenant owner role required.'], 403);
        }

        $tenant = $this->tenantContext->get();

        return response()->json([
            'data' => $this->teamSettingsService->getSettings($tenant),
        ]);
    }

    public function update(UpdateTeamSettingRequest $request, string $team_setting): JsonResponse
    {
        if (! $request->user()?->isTenantOwner()) {
            return response()->json(['message' => 'Forbidden. Tenant owner role required.'], 403);
        }

        $tenant = $this->tenantContext->get();
        $updatedTenant = $this->teamSettingsService->updateSettings($tenant, $request->validated('settings'));

        $this->activityLogService->record(
            tenant: $tenant,
            action: 'tenant.team_settings.updated',
            userId: $request->user()?->id,
            subjectType: 'tenant',
            subjectId: $updatedTenant->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            meta: ['settings' => $request->validated('settings')],
        );

        return response()->json([
            'message' => 'Team settings updated successfully.',
            'data' => $this->teamSettingsService->getSettings($updatedTenant),
        ]);
    }
}
