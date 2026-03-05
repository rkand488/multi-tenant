<?php

namespace App\Tenancy;

use Illuminate\Support\Facades\DB;
use Throwable;

class TenantQueryExecutor
{
    public function runTenant(callable $callback): mixed
    {
        $tenant = tenantOrNull();

        if ($tenant === null) {
            return null;
        }

        try {
            $connection = DB::connection(config('tenancy.tenant_connection', 'tenant'));

            return $callback($connection, $tenant);
        } catch (Throwable) {
            return null;
        }
    }
}
