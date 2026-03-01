<?php

namespace App\Tenancy;

use App\Central\Models\Domain;
use App\Central\Models\Tenant;
use App\Tenancy\Contracts\FindsTenant;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Resolves the current Tenant from an incoming HTTP request.
 *
 * Resolution order:
 *   1. Exact domain match in the `domains` table (supports custom CNAME domains).
 *   2. Slug extraction from the subdomain (e.g. "acme" from acme.app.com).
 *
 * The central domain (app.com / CENTRAL_DOMAIN) never triggers resolution —
 * the IdentifyTenant middleware only runs on non-central routes.
 */
class TenantResolver implements FindsTenant
{
    public function fromRequest(Request $request): Tenant
    {
        $host = $request->getHost();

        // ------------------------------------------------------------------
        // 1. Try an exact match against the domains table.
        //    This covers custom branded domains (e.g. crm.acmecorp.com).
        // ------------------------------------------------------------------
        $domain = Domain::query()
            ->where('domain', $host)
            ->with('tenant')
            ->first();

        if ($domain?->tenant !== null) {
            return $domain->tenant;
        }

        // ------------------------------------------------------------------
        // 2. Extract the slug from a standard subdomain: acme.app.com → acme.
        // ------------------------------------------------------------------
        $baseDomain = config('tenancy.domain'); // e.g. "app.com"

        if (str_ends_with($host, '.'.$baseDomain)) {
            $slug = substr($host, 0, strlen($host) - strlen('.'.$baseDomain));

            $tenant = Tenant::query()
                ->where('slug', $slug)
                ->first();

            if ($tenant !== null) {
                return $tenant;
            }
        }

        throw new NotFoundHttpException("No tenant found for host [{$host}].");
    }
}
