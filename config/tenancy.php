<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Central Domain
    |--------------------------------------------------------------------------
    |
    | The root domain that serves the marketing site, registration pages, and
    | the super-admin dashboard.  This domain never activates tenancy.
    |
    */

    'central_domain' => env('CENTRAL_DOMAIN', 'tenantrix.com'),

    /*
    |--------------------------------------------------------------------------
    | Application Domain
    |--------------------------------------------------------------------------
    |
    | The base domain for auto-generated tenant subdomains.
    | A tenant with slug "acme" will be served at acme.{domain}.
    |
    */

    'domain' => env('APP_DOMAIN', 'tenantrix.com'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Database Prefix
    |--------------------------------------------------------------------------
    |
    | Every tenant database is named using this prefix followed by the tenant
    | slug.  For example: tenantrix_acme, tenantrix_globex.
    |
    */

    'db_prefix' => env('TENANT_DB_PREFIX', 'tenantrix_'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Queue Prefix
    |--------------------------------------------------------------------------
    |
    | Horizon queue names for tenant-scoped jobs use this prefix so that
    | per-tenant throughput can be monitored independently.
    |
    */

    'queue_prefix' => env('TENANT_QUEUE_PREFIX', 'tenantrix-'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Database Connection Name
    |--------------------------------------------------------------------------
    |
    | The key inside config/database.php that is dynamically reconfigured
    | at runtime when a tenant request is identified.
    |
    */

    'tenant_connection' => 'tenant',

    /*
    |--------------------------------------------------------------------------
    | Central Database Connection Name
    |--------------------------------------------------------------------------
    |
    | The always-on connection used for central (landlord) models: Tenant,
    | Domain, Plan, Subscription, Invoice, SuperAdmin.
    |
    */

    'central_connection' => 'central',

];
