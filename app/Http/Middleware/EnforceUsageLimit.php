<?php

namespace App\Http\Middleware;

use App\Billing\Services\UsageService;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abort with 429 Too Many Requests if the tenant has exceeded the metered
 * usage limit for a given feature in the current billing period.
 *
 * Usage: ->middleware('usage.limit:api_calls')
 */
class EnforceUsageLimit
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly UsageService $usageService,
    ) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $tenant = $this->tenantContext->getOrNull();

        if (! $tenant) {
            return response()->json(['message' => 'Tenant not identified.'], Response::HTTP_UNAUTHORIZED);
        }

        if (! $this->usageService->canUse($tenant, $feature)) {
            $subscription = $tenant->currentSubscription()->with('plan')->first();
            $limit = $subscription?->feature($feature);
            $used = $this->usageService->currentUsage($tenant, $feature, $subscription);

            return response()->json([
                'message' => "You have reached the {$feature} limit for your current billing period.",
                'code' => 'usage_limit_exceeded',
                'feature' => $feature,
                'used' => $used,
                'limit' => $limit,
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        return $next($request);
    }
}
