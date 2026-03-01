<?php

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abort with 402 Payment Required if the current tenant does not have an
 * accessible subscription (active, trialing, or within a grace period).
 */
class RequireActiveSubscription
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->tenantContext->get();

        if (! $tenant) {
            return response()->json(['message' => 'Tenant not identified.'], Response::HTTP_UNAUTHORIZED);
        }

        $subscription = $tenant->currentSubscription()->with('plan')->first();

        if (! $subscription?->hasAccess()) {
            return response()->json([
                'message' => 'An active subscription is required to access this resource.',
                'code' => 'subscription_required',
            ], Response::HTTP_PAYMENT_REQUIRED);
        }

        return $next($request);
    }
}
