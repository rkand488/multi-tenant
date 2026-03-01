<?php

namespace App\Http\Middleware;

use App\Central\Enums\TenantStatus;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards every tenant route against suspended or cancelled accounts.
 *
 * Must be applied AFTER IdentifyTenant so that the TenantContext is already
 * populated.  Returns a JSON error for API routes and an Inertia/blade page
 * for web routes.
 *
 * Registration (bootstrap/app.php):
 *   $middleware->alias(['tenant.active' => EnsureTenantIsActive::class]);
 */
class EnsureTenantIsActive
{
    public function __construct(
        private readonly TenantContext $context,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->context->get();

        if ($tenant->status !== TenantStatus::Active) {
            return $this->deniedResponse($request, $tenant->status);
        }

        return $next($request);
    }

    private function deniedResponse(Request $request, TenantStatus $status): Response
    {
        $message = match ($status) {
            TenantStatus::Suspended => 'This workspace has been suspended. Please contact support.',
            TenantStatus::Cancelled => 'This workspace subscription has been cancelled.',
            TenantStatus::Provisioning => 'This workspace is still being set up. Please wait a moment.',
            default => 'Access to this workspace is not available.',
        };

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'status' => $status->value,
            ], Response::HTTP_FORBIDDEN);
        }

        // For Inertia / web requests — render a simple blade view.
        // Swap this for an Inertia::render() call once the frontend is wired up.
        abort(Response::HTTP_FORBIDDEN, $message);
    }
}
