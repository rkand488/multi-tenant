<?php

namespace App\Http\Controllers\Api\Billing;

use App\Billing\Services\UsageService;
use App\Http\Controllers\Controller;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UsageController extends Controller
{
    public function __construct(
        private readonly UsageService $usageService,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * Summary of all feature usage vs limits for the current billing period.
     */
    public function index(): JsonResponse
    {
        $tenant = $this->tenantContext->get();

        return response()->json(['data' => $this->usageService->summary($tenant)]);
    }

    /**
     * Usage for a single feature key.
     */
    public function show(string $feature): JsonResponse
    {
        $tenant = $this->tenantContext->get();
        $subscription = $tenant->currentSubscription()->with('plan')->first();

        if (! $subscription) {
            return response()->json(['message' => 'No active subscription.'], Response::HTTP_NOT_FOUND);
        }

        $used = $this->usageService->currentUsage($tenant, $feature, $subscription);
        $limit = $subscription->feature($feature);

        return response()->json([
            'data' => [
                'feature' => $feature,
                'used' => $used,
                'limit' => is_numeric($limit) ? (int) $limit : $limit,
                'has_limit' => is_numeric($limit),
                'can_use' => $this->usageService->canUse($tenant, $feature),
            ],
        ]);
    }
}
