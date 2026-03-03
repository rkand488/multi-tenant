<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantOrSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->isSuperAdmin() && ! $user->isTenantOwner() && ! $user->isTenantUser())) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden. Tenant or super admin access required.',
                ], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, 'Forbidden. Tenant or super admin access required.');
        }

        return $next($request);
    }
}
