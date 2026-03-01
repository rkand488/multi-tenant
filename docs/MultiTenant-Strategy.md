# Multi-Tenant Strategy

> **Isolation model:** Database-per-Tenant  
> **Routing model:** Subdomain-based (`{slug}.app.com`)

---

## 1. Isolation Model Decision: Database-per-Tenant

### Why Not Single Database with `tenant_id`?

| Concern | Single DB + `tenant_id` | Database-per-Tenant (chosen) |
|---|---|---|
| Data leakage risk | High — one missing `where tenant_id = ?` exposes all tenants | None — wrong DB = wrong data |
| Backup granularity | Must filter exports | Dump one DB per tenant |
| Schema customisation | Impossible without complex overrides | Run tenant-specific migrations |
| Compliance (GDPR) | Hard to isolate PII per client | Drop one DB to delete all PII |
| Query performance | Shared table bloat, index contention | Tables sized per tenant |
| Database per plan | Cannot offer dedicated server per enterprise tenant | Trivial — point tenant to own server |

### Trade-offs Accepted

- **Higher operational overhead:** many databases to manage → mitigated by automation and Horizon jobs.
- **Connection pooling:** PgBouncer/ProxySQL needed at scale → defined in ops runbook for 500+ tenants.
- **Migration effort:** each tenant DB must be migrated → `TenantMigrationJob` runs in parallel queues.

---

## 2. Database Layout

```
Central MySQL instance
├── landlord                 ← always used for central models
│   ├── tenants
│   ├── domains
│   ├── plans
│   ├── subscriptions
│   ├── invoices
│   └── super_admins
│
├── tenant_acme              ← created on onboarding
│   ├── users
│   ├── roles
│   ├── permissions
│   ├── role_user
│   ├── permission_role
│   ├── activity_logs
│   ├── audit_logs
│   ├── settings
│   └── files
│
├── tenant_globex
└── tenant_{slug} ...
```

Each tenant database name is derived deterministically: `tenant_{slug}` (slugified, max 48 chars).  
For enterprise tenants on a dedicated server, the connection credentials are stored encrypted in `tenants.db_connection` JSON column.

---

## 3. Subdomain Routing

### Nginx Wildcard Virtual Host

```nginx
# /etc/nginx/sites-available/saas-app
server {
    listen 443 ssl http2;
    server_name ~^(?<subdomain>.+)\.app\.com$;

    ssl_certificate     /etc/ssl/certs/wildcard.app.com.crt;
    ssl_certificate_key /etc/ssl/private/wildcard.app.com.key;

    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass   unix:/run/php/php8.3-fpm.sock;
        fastcgi_index  index.php;
        include        fastcgi_params;
        fastcgi_param  SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }
}

# Central domain (no subdomain)
server {
    listen 443 ssl http2;
    server_name app.com www.app.com;
    # ... same PHP-FPM config
}
```

### Route Groups in Laravel

```php
// routes/web.php

// Central routes — no subdomain
Route::domain(config('app.central_domain'))->group(base_path('routes/central.php'));

// Tenant routes — any subdomain
Route::domain('{tenant}.'.config('app.domain'))->group(base_path('routes/tenant.php'));
```

---

## 4. Tenant Resolver

`app/Support/Tenancy/TenantResolver.php`

```php
<?php

namespace App\Support\Tenancy;

use App\Central\Models\Domain;
use App\Central\Models\Tenant;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TenantResolver
{
    public function fromRequest(Request $request): Tenant
    {
        $host = $request->getHost();

        // 1. Try exact domain match (custom domains)
        $domain = Domain::query()
            ->where('domain', $host)
            ->with('tenant')
            ->first();

        if ($domain) {
            return $domain->tenant;
        }

        // 2. Try subdomain extraction
        $centralDomain = config('app.domain'); // app.com
        if (str_ends_with($host, ".{$centralDomain}")) {
            $slug = str_replace(".{$centralDomain}", '', $host);

            return Tenant::query()
                ->where('slug', $slug)
                ->firstOrFail();
        }

        throw new NotFoundHttpException("Tenant not found for host: {$host}");
    }
}
```

---

## 5. Tenant Context Singleton

`app/Support/Tenancy/TenantContext.php`

```php
<?php

namespace App\Support\Tenancy;

use App\Central\Models\Tenant;
use RuntimeException;

class TenantContext
{
    private ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): Tenant
    {
        if (! $this->tenant) {
            throw new RuntimeException('No tenant in context. Was IdentifyTenant middleware applied?');
        }

        return $this->tenant;
    }

    public function check(): bool
    {
        return $this->tenant !== null;
    }

    public function forget(): void
    {
        $this->tenant = null;
    }
}
```

Registered as a **singleton** in `TenancyServiceProvider`:

```php
$this->app->singleton(TenantContext::class);
```

A global helper provides convenient access:

```php
function tenant(): Tenant
{
    return app(TenantContext::class)->get();
}
```

---

## 6. Database Manager

`app/Support/Tenancy/DatabaseManager.php`

```php
<?php

namespace App\Support\Tenancy;

use App\Central\Models\Tenant;
use Illuminate\Support\Facades\DB;

class DatabaseManager
{
    public function connectTenant(Tenant $tenant): void
    {
        $config = $this->buildConnectionConfig($tenant);

        config(['database.connections.tenant' => $config]);

        DB::purge('tenant');
        DB::reconnect('tenant');
        DB::setDefaultConnection('tenant');
    }

    public function connectCentral(): void
    {
        DB::setDefaultConnection('central');
    }

    private function buildConnectionConfig(Tenant $tenant): array
    {
        // If tenant has a custom DB server, use it; otherwise use shared host
        $override = $tenant->db_connection ?? [];

        return array_merge([
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => "tenant_{$tenant->slug}",
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
        ], $override);
    }
}
```

---

## 7. Middleware Stack

### `IdentifyTenant` Middleware

```php
<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\DatabaseManager;
use App\Support\Tenancy\TenantContext;
use App\Support\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;

class IdentifyTenant
{
    public function __construct(
        private readonly TenantResolver $resolver,
        private readonly TenantContext $context,
        private readonly DatabaseManager $dbManager,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $tenant = $this->resolver->fromRequest($request);

        $this->context->set($tenant);
        $this->dbManager->connectTenant($tenant);

        return $next($request);
    }

    public function terminate(Request $request, mixed $response): void
    {
        $this->context->forget();
        $this->dbManager->connectCentral();
    }
}
```

### `EnsureTenantIsActive` Middleware

```php
public function handle(Request $request, Closure $next): mixed
{
    $tenant = tenant();

    if ($tenant->status !== TenantStatus::Active) {
        return match (true) {
            $request->expectsJson() => response()->json(['message' => 'Tenant suspended.'], 403),
            default => Inertia::render('Errors/TenantSuspended'),
        };
    }

    return $next($request);
}
```

### Middleware Registration (`bootstrap/app.php`)

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
    ]);

    $middleware->alias([
        'tenant'        => \App\Http\Middleware\IdentifyTenant::class,
        'tenant.active' => \App\Http\Middleware\EnsureTenantIsActive::class,
        'tenant.plan'   => \App\Http\Middleware\CheckPlanLimit::class,
    ]);
})
```

---

## 8. Tenant Onboarding Flow

```
POST /register
  →  Validate input (TenantRegistrationRequest)
  →  Create Tenant record (central DB, status=provisioning)
  →  Create Domain record
  →  Dispatch ProvisionTenantDatabase job
        → Create MySQL database: CREATE DATABASE tenant_{slug}
        -> Run tenant migrations: Artisan::call('migrate', ['--database' => 'tenant', ...])
        -> Seed default roles & permissions
        -> Create owner User record in tenant DB
        -> Create default Settings rows
        -> Update tenant status = active
  →  Dispatch SendTenantWelcomeEmail job
  →  Return 202 Accepted with provisioning status URL
```

---

## 9. Queue Tenancy

Jobs that run in tenant context carry `tenant_id` and re-initialise tenancy:

```php
<?php

namespace App\Support\Traits;

use App\Central\Models\Tenant;
use App\Support\Tenancy\DatabaseManager;
use App\Support\Tenancy\TenantContext;

trait TenantAwareJob
{
    public int $tenantId;

    public function initializeTenancy(): void
    {
        $tenant = Tenant::find($this->tenantId);

        app(TenantContext::class)->set($tenant);
        app(DatabaseManager::class)->connectTenant($tenant);
    }

    public function tags(): array
    {
        return ["tenant:{$this->tenantId}"];
    }
}
```

Horizon queue config ensures tenant jobs stay on dedicated queues:

```php
'tenant-worker' => [
    'connection' => 'redis',
    'queue'      => ['tenant-high', 'tenant-default', 'tenant-low'],
    'balance'    => 'auto',
    'processes'  => 10,
    'tries'      => 3,
],
```

---

*Last updated: 2026-03-01*
