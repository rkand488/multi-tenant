<?php

namespace App\Http\Controllers\Tenant;

use App\Central\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateTenantUserRoleRequest;
use App\Tenancy\TenantContext;
use App\Tenant\Services\TenantActivityLogService;
use App\Tenant\Services\TenantRolePermissionService;
use App\Tenant\Services\TenantUserService;
use Illuminate\Http\JsonResponse;

class RolePermissionController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly TenantUserService $tenantUserService,
        private readonly TenantRolePermissionService $rolePermissionService,
        private readonly TenantActivityLogService $activityLogService,
    ) {}

    public function index(): JsonResponse
    {
        if (! request()->user()?->isTenantOwner()) {
            return response()->json(['message' => 'Forbidden. Tenant owner role required.'], 403);
        }

        return response()->json([
            'data' => $this->rolePermissionService->getRolePermissionMatrix(),
        ]);
    }

    public function update(UpdateTenantUserRoleRequest $request, string $user): JsonResponse
    {
        if (! $request->user()?->isTenantOwner()) {
            return response()->json(['message' => 'Forbidden. Tenant owner role required.'], 403);
        }

        $tenant = $this->tenantContext->get();
        $tenantUser = $this->tenantUserService->findUser($tenant->id, $user);
        $updatedUser = $this->rolePermissionService->assignRole(
            $tenantUser,
            UserRole::from($request->string('role')->toString())
        );

        $this->activityLogService->record(
            tenant: $tenant,
            action: 'tenant.roles.assigned',
            userId: $request->user()?->id,
            subjectType: 'user',
            subjectId: $tenantUser->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            meta: ['role' => $updatedUser->role->value],
        );

        return response()->json([
            'message' => 'User role updated successfully.',
            'data' => $updatedUser,
        ]);
    }
}
