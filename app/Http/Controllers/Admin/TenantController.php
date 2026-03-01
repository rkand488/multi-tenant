<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Services\AdminTenantService;
use App\Central\Enums\TenantStatus;
use App\Central\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexTenantsRequest;
use App\Http\Requests\Admin\UpdateTenantStatusRequest;
use Illuminate\Http\JsonResponse;

class TenantController extends Controller
{
    public function __construct(
        private readonly AdminTenantService $tenantService,
    ) {}

    public function index(IndexTenantsRequest $request): JsonResponse
    {
        $tenants = $this->tenantService->listTenants(
            perPage: (int) $request->integer('per_page', 15),
            search: $request->string('search')->toString() ?: null,
            status: $request->filled('status') ? TenantStatus::from($request->string('status')->toString()) : null,
        );

        return response()->json($tenants);
    }

    public function show(Tenant $tenant): JsonResponse
    {
        return response()->json([
            'data' => $tenant->load(['primaryDomain', 'currentSubscription.plan']),
        ]);
    }

    public function update(UpdateTenantStatusRequest $request, Tenant $tenant): JsonResponse
    {
        $status = TenantStatus::from($request->string('status')->toString());
        $updatedTenant = $this->tenantService->updateStatus($tenant, $status);

        return response()->json([
            'message' => $status === TenantStatus::Suspended
                ? 'Tenant suspended successfully.'
                : 'Tenant status updated successfully.',
            'data' => $updatedTenant,
        ]);
    }
}
