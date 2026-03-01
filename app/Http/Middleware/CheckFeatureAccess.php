<?php

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abort with 403 Forbidden if the current tenant's plan does not include a
 * boolean feature flag.
 *
 * Usage: ->middleware('feature:api_access')
 */
class CheckFeatureAccess
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $tenant = $this->tenantContext->get();

        if (! $tenant) {
            return response()->json(['message' => 'Tenant not identified.'], Response::HTTP_UNAUTHORIZED);
        }

        $subscription = $tenant->currentSubscription()->with('plan')->first();

        if (! $subscription?->hasFeature($feature)) {
            return response()->json([
                'message' => "Your current plan does not include access to the '{$feature}' feature.",
                'code' => 'feature_not_available',
                'feature' => $feature,
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
