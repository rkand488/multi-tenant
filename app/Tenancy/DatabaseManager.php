<?php

namespace App\Tenancy;

use App\Central\Models\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Switches the application's active database connection to the given tenant's
 * isolated database, then restores the central connection when the request
 * lifecycle ends.
 *
 * The "tenant" connection in config/database.php acts as a mutable slot.
 * This class fills that slot at runtime from the resolved Tenant record,
 * calling DB::purge() to discard any previously cached PDO instance and
 * DB::reconnect() to open a fresh connection with the new credentials.
 */
class DatabaseManager
{
    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Point the "tenant" connection at the given tenant's database and make
     * it the default connection for Eloquent queries.
     */
    public function connectTenant(Tenant $tenant): void
    {
        $connectionName = config('tenancy.tenant_connection');

        config(['database.connections.'.$connectionName => $this->buildConnectionConfig($tenant)]);

        DB::purge($connectionName);
        DB::reconnect($connectionName);
        DB::setDefaultConnection($connectionName);
    }

    /**
     * Restore the "central" connection as the default, used inside
     * IdentifyTenant::terminate() after the response has been sent.
     */
    public function connectCentral(): void
    {
        DB::setDefaultConnection(config('tenancy.central_connection'));
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /**
     * Build the connection config array for a tenant.
     *
     * If the tenant has a custom `db_connection` JSON blob (set for enterprise
     * tenants on dedicated servers) those values override the shared defaults.
     *
     * @return array<string, mixed>
     */
    private function buildConnectionConfig(Tenant $tenant): array
    {
        $defaults = [
            'driver' => 'mysql',
            'host' => config('database.connections.mysql.host', '127.0.0.1'),
            'port' => config('database.connections.mysql.port', '3306'),
            'database' => $tenant->databaseName(),
            'username' => config('database.connections.mysql.username', 'root'),
            'password' => config('database.connections.mysql.password', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];

        // Enterprise tenants may store their own server credentials.
        $override = $tenant->db_connection ?? [];

        return array_merge($defaults, $override);
    }
}
