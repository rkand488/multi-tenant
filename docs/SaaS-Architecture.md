# SaaS Architecture — Multi-Tenant Laravel Application

> **Status:** Production-Ready Design  
> **Stack:** Laravel 12 · PHP 8.3 · MySQL 8 · Redis · Inertia.js · Vue 3 · Yarn · Docker

---

## 1. System Overview

This application is a **database-per-tenant SaaS platform** where each paying tenant (organisation) receives:

- An isolated MySQL database
- A dedicated subdomain (`{slug}.app.com`)
- Scoped queues, cache namespaces, and file storage
- Full role-based access control within their workspace

The central (landlord) database manages tenants, plans, subscriptions, billing records, and super-admin access. Tenant databases contain all business data and are created automatically during onboarding.

```
┌─────────────────────────────────────────────────────────────────┐
│                         INTERNET                                │
└───────────────────────────┬─────────────────────────────────────┘
                            │
              ┌─────────────▼──────────────┐
              │     Nginx Reverse Proxy     │
              │  (wildcard *.app.com TLS)   │
              └──────┬──────────┬───────────┘
                     │          │
          ┌──────────▼──┐  ┌────▼────────────┐
          │  app.com     │  │ {slug}.app.com  │
          │  (Central)   │  │  (Tenant)       │
          └──────┬───────┘  └────┬────────────┘
                 │               │
         ┌───────▼───────────────▼──────────┐
         │       Laravel Application         │
         │   TenantResolver Middleware       │
         │   identifies context per request  │
         └──────┬───────────┬───────────────┘
                │           │
    ┌───────────▼──┐  ┌──────▼─────────────────┐
    │  Central DB  │  │  Tenant DB (per tenant) │
    │  (landlord)  │  │  tenant_{slug}          │
    └──────────────┘  └─────────────────────────┘
                │
    ┌───────────▼────────────────────────────────┐
    │  Redis (cache + queues + sessions)          │
    │  Namespace: tenant:{id}:*                   │
    └────────────────────────────────────────────┘
```

---

## 2. Technical Stack

| Layer | Technology | Rationale |
|---|---|---|
| Backend framework | Laravel 12 (PHP 8.3) | Robust ecosystem, expressive ORM, job system |
| Frontend | Inertia.js + Vue 3 | SPA feel without a separate API for the dashboard |
| Package manager | Yarn (Berry) | Faster installs, workspace support |
| Build tool | Vite | First-class Laravel support, fast HMR |
| Database (central) | MySQL 8.0 | ACID compliance, JSON columns, full-text search |
| Database (tenant) | MySQL 8.0 (per DB) | Full isolation, backup-per-tenant, no data leakage |
| Cache | Redis 7 | Tag-based invalidation, tenant namespacing |
| Queue | Redis via Laravel Horizon | Per-tenant queue naming, supervisor monitoring |
| Auth | Laravel Sanctum | SPA cookies + API tokens in one package |
| Storage | S3-compatible (per tenant prefix) | `/tenants/{id}/uploads/` isolation |
| HTTP server | Nginx + PHP-FPM | Battle-tested, wildcard vhost |
| Container | Docker + Docker Compose | Reproducible environment |
| CI/CD | GitHub Actions | Automated tests, Pint formatting, deploy |

---

## 3. Application Layers

```
app/
├── Central/                   # Landlord / super-admin domain
│   ├── Controllers/
│   │   ├── Auth/
│   │   ├── Admin/
│   │   └── Onboarding/
│   ├── Models/
│   │   ├── Tenant.php
│   │   ├── Domain.php
│   │   ├── Plan.php
│   │   ├── Subscription.php
│   │   └── Invoice.php
│   ├── Services/
│   │   ├── TenantProvisioner.php
│   │   ├── SubscriptionService.php
│   │   └── BillingService.php
│   ├── Jobs/
│   │   ├── ProvisionTenantDatabase.php
│   │   └── SendTenantWelcomeEmail.php
│   └── Policies/
│
├── Tenant/                    # Per-tenant domain
│   ├── Controllers/
│   │   ├── Auth/
│   │   ├── Dashboard/
│   │   ├── Users/
│   │   ├── Roles/
│   │   ├── Settings/
│   │   ├── Files/
│   │   └── Api/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Permission.php
│   │   ├── ActivityLog.php
│   │   ├── AuditLog.php
│   │   └── Setting.php
│   ├── Services/
│   │   ├── UserService.php
│   │   ├── RoleService.php
│   │   ├── FileService.php
│   │   ├── ActivityLogger.php
│   │   └── NotificationService.php
│   └── Policies/
│
├── Http/
│   ├── Middleware/
│   │   ├── IdentifyTenant.php       # resolves tenant from domain
│   │   ├── InitializeTenancyForHttp.php
│   │   ├── InitializeTenancyForQueue.php
│   │   └── EnsureTenantIsActive.php
│   └── Kernel.php (bootstrap/app.php in L12)
│
├── Support/
│   ├── Tenancy/
│   │   ├── TenantResolver.php
│   │   ├── TenantContext.php        # singleton holding current tenant
│   │   └── DatabaseManager.php     # switches DB connection
│   └── Traits/
│       ├── BelongsToTenant.php
│       └── HasActivityLog.php
│
└── Providers/
    ├── AppServiceProvider.php
    ├── TenancyServiceProvider.php
    └── HorizonServiceProvider.php
```

---

## 4. Request Lifecycle

### Central Request (`app.com/register`)

```
Browser → Nginx → PHP-FPM
  → Laravel bootstrap
  → Global middleware (CSRF, auth guard: web_central)
  → CentralController
  → CentralDB queries
  → Inertia response (Vue component)
```

### Tenant Request (`acme.app.com/dashboard`)

```
Browser → Nginx → PHP-FPM
  → Laravel bootstrap
  → IdentifyTenant middleware
      └── TenantResolver::fromDomain('acme.app.com')
          └── SELECT * FROM domains WHERE domain = ?  [CentralDB]
          └── SELECT * FROM tenants WHERE id = ?      [CentralDB]
  → TenantContext::set($tenant)
  → DatabaseManager::connectTenantDatabase($tenant)
      └── config(['database.connections.tenant' => [...]])
      └── DB::purge('tenant') + DB::reconnect('tenant')
  → EnsureTenantIsActive checks subscription status
  → TenantController runs against tenant DB
  → Redis cache with key prefix "tenant:{id}:"
  → Inertia response (Vue component)
```

---

## 5. Frontend Architecture (Vue 3 + Inertia)

```
resources/
├── js/
│   ├── app.js                      # Inertia bootstrap
│   ├── Central/
│   │   ├── Layouts/
│   │   │   ├── CentralLayout.vue
│   │   │   └── AdminLayout.vue
│   │   └── Pages/
│   │       ├── Auth/
│   │       │   ├── Login.vue
│   │       │   └── Register.vue
│   │       └── Admin/
│   │           ├── Dashboard.vue
│   │           ├── Tenants/
│   │           └── Plans/
│   └── Tenant/
│       ├── Layouts/
│       │   └── TenantLayout.vue
│       └── Pages/
│           ├── Dashboard.vue
│           ├── Users/
│           ├── Roles/
│           ├── Settings/
│           └── Files/
├── css/
│   └── app.css                     # Tailwind CSS v4 entry
└── views/
    └── app.blade.php               # Single Blade template for Inertia
```

**State management:** Pinia stores for auth user, tenant context, notifications, and plan limits.  
**Component library:** Headless UI + custom Tailwind components.

---

## 6. Queue Architecture

Laravel Horizon manages all queues. Each tenant gets tagged queues to ensure isolation and observability.

```
horizon.php queues config:
  Central supervisor → queues: [default, onboarding, billing, notifications]
  Tenant supervisor  → queues: [tenant-{id}, tenant-{id}-notifications]

Queue naming:  "tenant-{tenantId}"
Job tagging:   $job->tags() returns ['tenant:{id}']
```

All tenant jobs implement `TenantAwareJob` which stores `tenant_id` and re-initialises tenancy in `handle()`.

---

## 7. Caching Strategy

| Scope | Key pattern | TTL |
|---|---|---|
| Tenant config / settings | `tenant:{id}:settings` | 1 hour |
| User permissions | `tenant:{id}:user:{uid}:perms` | 30 min |
| Plan limits | `tenant:{id}:plan:limits` | 6 hours |
| API rate counters | `tenant:{id}:rate:{route}:{uid}` | 60 sec |
| Dashboard stats | `tenant:{id}:stats:daily` | 15 min |

All cache calls use the `tenantCache()` helper which automatically prefixes with `tenant:{id}:`.

---

## 8. File Storage

```
S3 bucket layout:
  /tenants/{tenantId}/
      avatars/
      documents/
      exports/
      imports/

Local disk (dev):
  storage/app/tenants/{tenantId}/
```

`FileService` enforces storage quotas from the tenant's active plan before accepting uploads.

---

## 9. Email & Notifications

- **Central emails:** tenant welcome, subscription invoices, super-admin alerts
- **Tenant emails:** user invitations, password resets, activity digests
- Laravel's notification system is used with each `Notification` class implementing `TenantNotification` interface
- Mail config is per-tenant (stored in `settings` table and merged at runtime for tenant context)

---

## 10. Error Handling & Observability

- **Sentry** (or Flare) integration with tenant context attached to every exception
- **Laravel Pail** for live log tailing in development
- Structured JSON logging in production with `tenant_id`, `user_id`, and `request_id` fields
- Horizon dashboard protected behind admin auth

---

*Last updated: 2026-03-01*
