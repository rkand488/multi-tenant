<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantUserRequest;
use App\Http\Requests\Tenant\UpdateTenantUserRequest;
use App\Models\User;
use App\Tenancy\TenantContext;
use App\Tenant\Services\TenantActivityLogService;
use App\Tenant\Services\TenantUserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly TenantUserService $tenantUserService,
        private readonly TenantActivityLogService $activityLogService,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $tenant = $this->tenantContext->get();
        $users = $this->tenantUserService->listUsers($tenant->id);

        return response()->json($users);
    }

    public function store(StoreTenantUserRequest $request): JsonResponse
    {
        if (! $request->user()?->isTenantOwner()) {
            return response()->json(['message' => 'Forbidden. Tenant owner role required.'], 403);
        }

        $tenant = $this->tenantContext->get();
        $user = $this->tenantUserService->createUser($tenant->id, $request->validated());

        $this->activityLogService->record(
            tenant: $tenant,
            action: 'tenant.users.created',
            userId: $request->user()?->id,
            subjectType: 'user',
            subjectId: $user->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json([
            'message' => 'User created successfully.',
            'data' => $user,
        ], 201);
    }

    public function show(string $user): JsonResponse
    {
        $tenant = $this->tenantContext->get();
        $tenantUser = $this->tenantUserService->findUser($tenant->id, $user);

        $this->authorize('view', $tenantUser);

        return response()->json(['data' => $tenantUser]);
    }

    public function update(UpdateTenantUserRequest $request, string $user): JsonResponse
    {
        $tenant = $this->tenantContext->get();
        $tenantUser = $this->tenantUserService->findUser($tenant->id, $user);

        $this->authorize('update', $tenantUser);

        $updatedUser = $this->tenantUserService->updateUser($tenantUser, $request->validated());

        $this->activityLogService->record(
            tenant: $tenant,
            action: 'tenant.users.updated',
            userId: $request->user()?->id,
            subjectType: 'user',
            subjectId: $tenantUser->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => $updatedUser,
        ]);
    }

    public function destroy(string $user): JsonResponse
    {
        $tenant = $this->tenantContext->get();
        $tenantUser = $this->tenantUserService->findUser($tenant->id, $user);

        $this->authorize('delete', $tenantUser);

        $this->tenantUserService->deleteUser($tenantUser);

        $this->activityLogService->record(
            tenant: $tenant,
            action: 'tenant.users.deleted',
            userId: request()->user()?->id,
            subjectType: 'user',
            subjectId: $tenantUser->id,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent(),
        );

        return response()->json(['message' => 'User deleted successfully.']);
    }
}
