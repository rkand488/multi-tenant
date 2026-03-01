# Implementation Plan

> **Project:** SaaS Multi-Tenant Platform — Laravel 12 · Inertia · Vue 3  
> **Isolation:** Database-per-Tenant · Subdomain routing (`{slug}.app.com`)  
> **Ref docs:** SaaS-Architecture.md · MultiTenant-Strategy.md · Database-Schema.md · API-Documentation.md · Security.md · Deployment.md · Testing.md

---

## How to Read This Document

Each stage is **self-contained and sequential**. Complete verification criteria before moving to the next stage. File paths are relative to the project root. Every `php artisan make:*` command is the canonical way to generate boilerplate; manually created files are noted explicitly.

**Conventions used:**

- `[create]` — new file, does not exist yet
- `[generate]` — created via `php artisan make:*`
- `[modify]` — existing file that needs editing
- `☐` — task not started

---

## Stage 1 — Project Setup

**Goal:** A running Laravel 12 application with Docker, Vite, Inertia + Vue 3, Tailwind v4, Yarn, CI, and a green baseline test suite.

**Estimated time:** 2–3 days

---

### 1.1 Composer & Node Dependencies

```bash
# PHP
composer require laravel/sanctum inertiajs/inertia-laravel tightenco/ziggy
composer require --dev laravel/pint pestphp/pest pestphp/pest-plugin-laravel

# Node (Yarn)
yarn add vue@3 @inertiajs/vue3 @vitejs/plugin-vue
yarn add -D tailwindcss @tailwindcss/vite autoprefixer
yarn add pinia @headlessui/vue
```

---

### 1.2 Files to Create

#### Docker & Infrastructure

| File | Type | Purpose |
|---|---|---|
| `docker-compose.yml` | [create] | Defines app, nginx, mysql, redis, horizon, scheduler, mailpit, minio services |
| `docker-compose.override.yml` | [create] | Dev-only overrides (mailpit, minio, xdebug) |
| `docker/php/Dockerfile` | [create] | Multi-stage PHP 8.3-FPM image (base → development → production) |
| `docker/php/php-dev.ini` | [create] | Xdebug + memory settings for development |
| `docker/php/php-prod.ini` | [create] | OPcache + hardened settings for production |
| `docker/nginx/conf.d/default.conf` | [create] | Wildcard `*.app.com` vhost + central `app.com` vhost |
| `docker/mysql/init/01_create_landlord.sql` | [create] | `CREATE DATABASE IF NOT EXISTS landlord;` |
| `.dockerignore` | [create] | Exclude node_modules, .git, storage/app |

#### Application Configuration

| File | Type | Purpose |
|---|---|---|
| `.env.example` | [modify] | Add all project-specific env keys (see Deployment.md §2) |
| `.env.testing` | [create] | SQLite in-memory, queue=sync, mail=array |
| `config/database.php` | [modify] | Add `central` and `tenant` named connections alongside default |
| `config/tenancy.php` | [create] | `central_domain`, `tenant_domain`, `db_prefix`, `queue_prefix` |
| `bootstrap/app.php` | [modify] | Register tenancy middleware aliases; configure exception handler |

#### Frontend Scaffold

| File | Type | Purpose |
|---|---|---|
| `vite.config.js` | [modify] | Add `@vitejs/plugin-vue`, `@tailwindcss/vite`, alias `@` → `resources/js` |
| `resources/css/app.css` | [modify] | Tailwind v4 `@import "tailwindcss"` entry point |
| `resources/js/app.js` | [modify] | Inertia `createInertiaApp` bootstrap with Vue 3 |
| `resources/js/bootstrap.js` | [modify] | Axios + Ziggy setup |
| `resources/views/app.blade.php` | [create] | Single Inertia root template (replaces `welcome.blade.php`) |
| `resources/js/Central/Layouts/CentralLayout.vue` | [create] | Shared layout for central pages |
| `resources/js/Central/Layouts/AdminLayout.vue` | [create] | Admin dashboard layout with sidebar |
| `resources/js/Tenant/Layouts/TenantLayout.vue` | [create] | Tenant dashboard layout with nav |
| `resources/js/Components/` | [create] | Shared UI components directory (Button, Input, Modal, etc.) |

#### CI/CD

| File | Type | Purpose |
|---|---|---|
| `.github/workflows/ci.yml` | [create] | lint → test → analyse pipeline (see Deployment.md §3) |
| `.github/workflows/deploy.yml` | [create] | Deploy to staging on `main` push; production on version tag |
| `deploy.sh` | [create] | Zero-downtime deploy script (pull → migrate → cache → restart) |

#### Code Quality

| File | Type | Purpose |
|---|---|---|
| `pint.json` | [create] or [modify] | Laravel preset with project-specific rules |
| `phpstan.neon` | [create] | Level 9 analysis, `paths: [app]`, custom bootstrap |
| `.editorconfig` | [create] | 4-space indent for PHP, 2-space for JS/Vue |

---

### 1.3 Routes

| File | Type | Purpose |
|---|---|---|
| `routes/web.php` | [modify] | Split into central domain group + tenant subdomain group |
| `routes/central.php` | [create] | All `app.com/*` web routes (register, login, admin) |
| `routes/tenant.php` | [create] | All `{slug}.app.com/*` web routes (dashboard, users, etc.) |
| `routes/api.php` | [modify] | Mount central API at `/api/v1/` with version prefix |
| `routes/console.php` | [modify] | Scheduled commands registration |

---

### 1.4 Providers

| File | Type | Purpose |
|---|---|---|
| `app/Providers/AppServiceProvider.php` | [modify] | Register global helpers, macros, Vite config |
| `app/Providers/TenancyServiceProvider.php` | [create] | Singleton `TenantContext`; bind `TenantResolver`, `DatabaseManager` |
| `app/Providers/AuthServiceProvider.php` | [create] | Register policies; `Gate::before` for super-admin bypass |
| `app/Providers/HorizonServiceProvider.php` | [create] | Horizon auth gate (admin-only access) |
| `bootstrap/providers.php` | [modify] | Register `TenancyServiceProvider`, `AuthServiceProvider`, `HorizonServiceProvider` |

---

### 1.5 Tests

| File | Type | Covers |
|---|---|---|
| `tests/Pest.php` | [modify] | Add `createTenant()`, `actingAsTenantUser()`, `initializeTenancy()` global helpers |
| `tests/TestCase.php` | [modify] | `RefreshDatabase` on correct connection; helper traits |
| `tests/Architecture/ArchTest.php` | [create] | Central models in central namespace; Tenant models in tenant namespace; no `env()` in controllers; service classes not `new`-ed in controllers |
| `tests/Feature/Central/Auth/SuperAdminLoginTest.php` | [create] | Admin can log in; wrong password returns 422; locked after 5 attempts |

**Stage 1 verification:**

```bash
docker compose up -d
php artisan migrate --database=central
yarn build
php artisan test --compact
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

All pass → proceed to Stage 2.

---

## Stage 2 — Tenant Infrastructure

**Goal:** Tenant resolution, database switching, and the complete central database schema are working. A request to any subdomain correctly identifies and connects to the right tenant database.

**Estimated time:** 3–4 days

---

### 2.1 Migrations (Central — `database/migrations/central/`)

| File | Purpose |
|---|---|
| `create_tenants_table.php` | UUID PK, slug, status enum, owner_email, trial_ends_at, db_connection JSON, soft-deletes |
| `create_domains_table.php` | tenant_id FK, domain unique, is_primary, is_verified, verified_at |
| `create_plans_table.php` | name, slug, price_monthly, price_yearly, stripe_price_ids, features JSON, is_active, is_public |
| `create_subscriptions_table.php` | tenant_id FK, plan_id FK, status enum, billing_cycle, stripe_subscription_id, period dates |
| `create_invoices_table.php` | tenant_id FK, subscription_id FK, number unique, status enum, amounts, stripe_invoice_id |
| `create_super_admins_table.php` | name, email unique, password, remember_token, last_login_at |

> Run with: `php artisan migrate --database=central`

---

### 2.2 Migrations (Tenant — `database/migrations/tenant/`)

These migrations are run **per tenant** at provisioning time (not via the standard `artisan migrate`).

| File | Purpose |
|---|---|
| `create_users_table.php` | name, email, password, email_verified_at, is_owner, status enum, invited_by FK, soft-deletes |
| `create_roles_table.php` | name unique, display_name, description, is_system |
| `create_permissions_table.php` | name unique, group, description (no timestamps needed) |
| `create_role_user_table.php` | Pivot: user_id + role_id composite PK |
| `create_permission_role_table.php` | Pivot: permission_id + role_id composite PK |
| `create_activity_logs_table.php` | user_id, event, subject morph, description, properties JSON, ip_address, user_agent |
| `create_audit_logs_table.php` | user_id, event enum, auditable morph, old_values JSON, new_values JSON, url, ip |
| `create_settings_table.php` | group, key, value text, is_encrypted — unique on (group, key) |
| `create_files_table.php` | user_id FK, disk, path, original_name, mime_type, size_bytes, collection, meta JSON, soft-deletes |
| `create_notifications_table.php` | Standard Laravel notifications table (id, type, notifiable morph, data JSON, read_at) |

---

### 2.3 Models (Central — `app/Central/Models/`)

| File | Key details |
|---|---|
| `Tenant.php` | UUID PK; `HasMany` domains, subscriptions, invoices; casts: `status → TenantStatus`, `db_connection → array`, `extra → array`; `currentSubscription()` scope; `activePlan()` helper |
| `Domain.php` | `BelongsTo` Tenant; `isPrimary()` scope |
| `Plan.php` | casts: `features → array`; `isActive()` scope; `feature(string $key)` helper for limit lookups |
| `Subscription.php` | `BelongsTo` Tenant, Plan; casts: `status → SubscriptionStatus`; `isActive()`, `isOnTrial()` helpers |
| `Invoice.php` | `BelongsTo` Tenant, Subscription; casts: `status → InvoiceStatus`; `generateNumber()` static |
| `SuperAdmin.php` | Implements `Authenticatable`; guard: `admin` |

---

### 2.4 Enums (`app/Central/Enums/`)

| File | Cases |
|---|---|
| `TenantStatus.php` | `Provisioning`, `Active`, `Suspended`, `Cancelled` |
| `SubscriptionStatus.php` | `Trialing`, `Active`, `PastDue`, `Cancelled`, `Paused` |
| `InvoiceStatus.php` | `Draft`, `Open`, `Paid`, `Void`, `Uncollectible` |

---

### 2.5 Support Classes (`app/Support/Tenancy/`)

| File | Responsibility |
|---|---|
| `TenantResolver.php` | `fromRequest(Request): Tenant` — tries exact domain match first, then slug extraction from subdomain |
| `TenantContext.php` | Singleton holding current `?Tenant`; `set()`, `get()`, `check()`, `forget()` |
| `DatabaseManager.php` | `connectTenant(Tenant)` and `connectCentral()` — builds connection config from tenant or env defaults; calls `DB::purge()` + `DB::reconnect()` |

**Global helper** (`app/Support/helpers.php` — autoloaded via `composer.json`):

```php
function tenant(): \App\Central\Models\Tenant { ... }
function tenantCache(): \Illuminate\Cache\Repository { ... }  // prefixed cache
```

---

### 2.6 Middleware (`app/Http/Middleware/`)

| File | Responsibility |
|---|---|
| `IdentifyTenant.php` | Resolves tenant from request host; calls `TenantContext::set()` + `DatabaseManager::connectTenant()`; `terminate()` tears down context |
| `EnsureTenantIsActive.php` | Returns 403/Inertia error page if `tenant()->status !== Active` |
| `InitializeTenancyForQueue.php` | Used by queue workers; restores tenant context from serialised `tenant_id` |

Register in `bootstrap/app.php`:

```php
$middleware->alias([
    'tenant'        => IdentifyTenant::class,
    'tenant.active' => EnsureTenantIsActive::class,
]);
```

---

### 2.7 Artisan Commands (`app/Console/Commands/`)

| File | Signature | Purpose |
|---|---|---|
| `MigrateTenants.php` | `tenants:migrate {--tenant=} {--fresh} {--seed} {--force}` | Iterates active tenants, connects each, runs `migrate` on tenant connection |
| `SeedTenant.php` | `tenants:seed {--tenant=} {--class=}` | Runs a seeder against a single tenant DB |

---

### 2.8 Seeders

| File | Purpose |
|---|---|
| `database/seeders/Central/PlanSeeder.php` | Seeds Starter, Pro, Enterprise plans with features JSON |
| `database/seeders/Central/SuperAdminSeeder.php` | Seeds one super admin from env credentials |
| `database/seeders/Tenant/RoleSeeder.php` | Seeds `owner`, `admin`, `member`, `viewer` system roles |
| `database/seeders/Tenant/PermissionSeeder.php` | Seeds all `resource.action` permissions grouped by resource |

---

### 2.9 Tests

| File | Covers |
|---|---|
| `tests/Unit/TenantResolverTest.php` | Resolves correct tenant from subdomain; resolves from custom domain; throws `NotFoundHttpException` for unknown host |
| `tests/Unit/DatabaseManagerTest.php` | Builds correct connection config from tenant; falls back to env defaults |
| `tests/Feature/Central/TenantInfrastructureTest.php` | HTTP request to `acme.app.com` sets correct tenant context; request to unknown subdomain returns 404 |
| `tests/Feature/Tenant/TenantIsolationTest.php` | User in tenant A cannot see data from tenant B via API |

---

## Stage 3 — Authentication System

**Goal:** Super-admin login, tenant user login (session + token), email verification, password reset, and password policy are all working.

**Estimated time:** 3–4 days

---

### 3.1 Controllers

#### Central Auth (`app/Central/Controllers/Auth/`)

| File | Methods | Route |
|---|---|---|
| `SuperAdminLoginController.php` | `showLogin()`, `login()`, `logout()` | `GET/POST /login`, `POST /logout` (guard: `admin`) |

#### Tenant Auth (`app/Tenant/Controllers/Auth/`)

| File | Methods | Route |
|---|---|---|
| `LoginController.php` | `showLogin()`, `login()`, `logout()` | `GET/POST /login`, `POST /logout` (guard: `tenant`) |
| `RegisteredUserController.php` | `store()` — for invited users accepting invites | `POST /register/accept` |
| `EmailVerificationController.php` | `notice()`, `verify()`, `resend()` | Standard Laravel verify routes |
| `PasswordResetController.php` | `requestForm()`, `sendLink()`, `resetForm()`, `reset()` | Standard Laravel password routes |
| `ProfileController.php` | `show()`, `update()`, `updatePassword()` | `GET/PATCH /profile` |

---

### 3.2 Form Requests (`app/Tenant/Requests/Auth/`)

| File | Rules |
|---|---|
| `LoginRequest.php` | `email` required email, `password` required string, `device_name` optional |
| `UpdatePasswordRequest.php` | `current_password` required, `password` Password rule (min 12, mixed case, numbers, symbols) confirmed |
| `UpdateProfileRequest.php` | `name` required, `email` unique:users ignoring current user |

---

### 3.3 Guards & Auth Config

Modify `config/auth.php`:

```
guards:
  web      → driver: session, provider: users (tenant DB)  
  admin    → driver: session, provider: super_admins (central DB)
  sanctum  → driver: sanctum  (uses guard from request context)

providers:
  users        → driver: eloquent, model: App\Tenant\Models\User
  super_admins → driver: eloquent, model: App\Central\Models\SuperAdmin
```

---

### 3.4 Inertia Pages

| File | Purpose |
|---|---|
| `resources/js/Central/Pages/Auth/Login.vue` | Super-admin login form |
| `resources/js/Tenant/Pages/Auth/Login.vue` | Tenant user login form |
| `resources/js/Tenant/Pages/Auth/VerifyEmail.vue` | Email verification notice |
| `resources/js/Tenant/Pages/Auth/ForgotPassword.vue` | Password reset request form |
| `resources/js/Tenant/Pages/Auth/ResetPassword.vue` | New password form (signed URL) |

---

### 3.5 Notifications (`app/Tenant/Notifications/`)

| File | Channel | Trigger |
|---|---|---|
| `VerifyEmailNotification.php` | `mail` | On registration / resend request |
| `PasswordResetNotification.php` | `mail` | On password reset request |

---

### 3.6 Tests

| File | Covers |
|---|---|
| `tests/Feature/Central/Auth/SuperAdminLoginTest.php` | Login succeeds; wrong password; redirects to admin dashboard |
| `tests/Feature/Tenant/Auth/LoginTest.php` | Login with valid credentials; invalid credentials; unverified email blocked; suspended user blocked |
| `tests/Feature/Tenant/Auth/EmailVerificationTest.php` | Unverified user blocked from dashboard; verify endpoint works; expired link rejected; resend rate-limited |
| `tests/Feature/Tenant/Auth/PasswordResetTest.php` | Reset link sent; valid token resets password; expired token rejected |
| `tests/Unit/PasswordPolicyTest.php` | Password rule enforced: min 12 chars, mixed case, numbers, symbols |

---

## Stage 4 — Tenant Onboarding

**Goal:** A visitor can register a new tenant, the system provisions a database, runs tenant migrations, seeds default data, creates the owner user, and marks the tenant as active.

**Estimated time:** 3–4 days

---

### 4.1 Controllers (Central — `app/Central/Controllers/Onboarding/`)

| File | Methods | Route |
|---|---|---|
| `RegistrationController.php` | `showForm()`, `register()` | `GET/POST /register` |
| `ProvisioningStatusController.php` | `show()` | `GET /onboarding/{tenant}/status` |

---

### 4.2 Form Requests (`app/Central/Requests/`)

| File | Rules |
|---|---|
| `TenantRegistrationRequest.php` | `name` required, `slug` required unique:tenants alpha_dash max:63, `owner_email` required email unique:tenants, `owner_name` required, `password` Password rule, `plan_id` required exists:plans |

---

### 4.3 Services (`app/Central/Services/`)

| File | Methods | Purpose |
|---|---|---|
| `TenantProvisioner.php` | `provision(array $data): Tenant` | Creates Tenant + Domain records; dispatches `ProvisionTenantDatabase` job; dispatches `SendTenantWelcomeEmail` job |

---

### 4.4 Jobs (`app/Central/Jobs/`)

| File | Queue | What it does |
|---|---|---|
| `ProvisionTenantDatabase.php` | `onboarding` | Creates MySQL database `tenant_{slug}`; runs tenant migrations via `Artisan::call`; seeds roles + permissions; creates owner User record in tenant DB; updates `tenant->status = Active` |
| `SendTenantWelcomeEmail.php` | `notifications` | Sends welcome email to owner with subdomain URL, login instructions |

---

### 4.5 Notifications (`app/Central/Notifications/`)

| File | Channel | Trigger |
|---|---|---|
| `TenantWelcomeNotification.php` | `mail` | After `ProvisionTenantDatabase` completes successfully |
| `TenantProvisionFailedNotification.php` | `mail` | On `ProvisionTenantDatabase` job failure (notify super admin) |

---

### 4.6 Inertia Pages

| File | Purpose |
|---|---|
| `resources/js/Central/Pages/Auth/Register.vue` | Tenant registration form with plan selection |
| `resources/js/Central/Pages/Onboarding/Provisioning.vue` | Polling status page shown while database is being created |

---

### 4.7 Traits (`app/Support/Traits/`)

| File | Purpose |
|---|---|
| `TenantAwareJob.php` | Stores `tenantId`; `initializeTenancy()` re-connects in `handle()`; `tags()` returns `["tenant:{id}"]` |

---

### 4.8 Tests

| File | Covers |
|---|---|
| `tests/Feature/Central/Onboarding/TenantProvisioningTest.php` | Registration creates Tenant + Domain rows; job is dispatched; duplicate slug rejected; duplicate owner email rejected |
| `tests/Feature/Central/Onboarding/ProvisionJobTest.php` | Job creates tenant database; runs migrations; seeds roles; creates owner user; status set to `active`; failed job sets status to `provisioning` + notifies admin |
| `tests/Unit/TenantProvisionerTest.php` | Provisioner creates correct records; dispatches correct jobs |

---

## Stage 5 — Role & Permission System

**Goal:** Roles and permissions are fully manageable per tenant. Every API and web action is gated by the `hasPermission` check backed by cached policy resolution.

**Estimated time:** 3 days

---

### 5.1 Models (Tenant — `app/Tenant/Models/`)

| File | Key details |
|---|---|
| `Role.php` | `BelongsToMany` User, Permission; `isSystem` cast; `scopeSystem()`, `scopeCustom()` |
| `Permission.php` | `BelongsToMany` Role; grouped by `group` column |
| `User.php` | `BelongsToMany` Role (via `role_user`); `hasPermission(string): bool` with Redis cache; `hasRole(string): bool`; `allPermissions(): Collection` |

---

### 5.2 Controllers (Tenant — `app/Tenant/Controllers/`)

| File | Methods | Route |
|---|---|---|
| `Roles/RoleController.php` | `index`, `store`, `show`, `update`, `destroy` | `GET/POST /roles`, `GET/PATCH/DELETE /roles/{role}` |
| `Roles/RolePermissionController.php` | `update` | `PUT /roles/{role}/permissions` |
| `Roles/UserRoleController.php` | `store`, `destroy` | `POST /users/{user}/roles`, `DELETE /users/{user}/roles/{role}` |

---

### 5.3 Form Requests (`app/Tenant/Requests/Roles/`)

| File | Rules |
|---|---|
| `CreateRoleRequest.php` | `name` required unique:roles alpha_dash, `display_name` required, `description` nullable |
| `SyncPermissionsRequest.php` | `permissions` required array, `permissions.*` exists:permissions,name |

---

### 5.4 Policies (`app/Tenant/Policies/`)

| File | Methods |
|---|---|
| `RolePolicy.php` | `viewAny`, `create`, `update`, `delete` — mapped to `roles.*` permissions; `delete` also forbids system roles |
| `UserPolicy.php` | `viewAny`, `view`, `update`, `delete`, `invite` |

---

### 5.5 Services (`app/Tenant/Services/`)

| File | Methods | Purpose |
|---|---|---|
| `RoleService.php` | `create(array): Role`, `syncPermissions(Role, array): void`, `assignToUser(User, Role): void`, `removeFromUser(User, Role): void` | Wraps role mutations; flushes permission cache after every change |

---

### 5.6 Inertia Pages

| File | Purpose |
|---|---|
| `resources/js/Tenant/Pages/Roles/Index.vue` | Role list with permission counts and edit actions |
| `resources/js/Tenant/Pages/Roles/Show.vue` | Role detail with permission checkbox grid |

---

### 5.7 Tests

| File | Covers |
|---|---|
| `tests/Unit/PermissionCheckTest.php` | `hasPermission` returns true/false correctly; cache is hit on second call; cache is flushed after role change |
| `tests/Feature/Tenant/Roles/CreateRoleTest.php` | Admin creates role; viewer cannot create role; duplicate name rejected; system roles not deletable |
| `tests/Feature/Tenant/Roles/AssignPermissionsTest.php` | Permissions synced correctly; unknown permission name rejected |

---

## Stage 6 — Subscription System

**Goal:** Tenants can subscribe to a plan, upgrade/downgrade, cancel, and receive invoices. Stripe webhook events update the DB in real time. Plan limits are enforced on every relevant request.

**Estimated time:** 4–5 days

---

### 6.1 Models — no new models; extend existing

Add to `Tenant.php`:

- `activePlan(): ?Plan` — shortcut through `currentSubscription`
- `planFeature(string $key, mixed $default = null)` — reads from `Plan::features`

---

### 6.2 Controllers (Central — `app/Central/Controllers/`)

| File | Methods | Route |
|---|---|---|
| `Billing/PlanController.php` | `index` (public) | `GET /api/v1/billing/plans` |
| `Billing/WebhookController.php` | `handle()` | `POST /api/v1/webhooks/stripe` |

#### Controllers (Tenant — `app/Tenant/Controllers/`)

| File | Methods | Route |
|---|---|---|
| `Billing/SubscriptionController.php` | `show`, `store`, `update`, `destroy` | `GET/POST/PATCH/DELETE /api/v1/billing/subscription` |
| `Billing/InvoiceController.php` | `index`, `show` | `GET /api/v1/billing/invoices` |
| `Billing/PortalController.php` | `create` | `POST /api/v1/billing/portal` |

---

### 6.3 Form Requests (`app/Central/Requests/Billing/`)

| File | Rules |
|---|---|
| `SubscribeRequest.php` | `plan_id` required exists:plans,id, `billing_cycle` required in:monthly,yearly |
| `ChangePlanRequest.php` | `plan_id` required exists:plans different from current |

---

### 6.4 Services (`app/Central/Services/`)

| File | Methods | Purpose |
|---|---|---|
| `SubscriptionService.php` | `subscribe(Tenant, Plan, string $cycle): Subscription`, `changePlan(Subscription, Plan): void`, `cancel(Subscription): void`, `resume(Subscription): void` | Manages subscription lifecycle; creates Invoice on each period |
| `BillingService.php` | `createInvoice(Tenant, Subscription): Invoice`, `markInvoicePaid(Invoice): void`, `generateInvoiceNumber(): string` | Invoice creation/management; sequential number (`INV-2026-00001`) |
| `StripeWebhookHandler.php` | `handle(array $payload): void` — dispatches to typed methods per event | Processes `invoice.paid`, `invoice.payment_failed`, `customer.subscription.deleted`, `customer.subscription.updated` |

---

### 6.5 Middleware (`app/Http/Middleware/`)

| File | Constructor args | Purpose |
|---|---|---|
| `CheckPlanLimit.php` | `string $feature`, `callable $usageResolver` | Reads `tenant()->planFeature($feature)`; counts current usage; returns 422 if limit exceeded |

Usage in routes:

```php
Route::post('/users/invite')->middleware('tenant.plan:max_users,countTenantUsers');
Route::post('/files')->middleware('tenant.plan:storage_gb,currentStorageGb');
```

---

### 6.6 Jobs (`app/Central/Jobs/`)

| File | Queue | Purpose |
|---|---|---|
| `GenerateMonthlyInvoices.php` | `billing` | Runs monthly via scheduler; creates Invoice records for active subscriptions |
| `SuspendPastDueTenants.php` | `billing` | Runs daily; suspends tenants with subscription `past_due` beyond grace period |

---

### 6.7 Inertia Pages

| File | Purpose |
|---|---|
| `resources/js/Central/Pages/Auth/Register.vue` | Plan picker step in registration (from Stage 4) — wire up plan data |
| `resources/js/Tenant/Pages/Billing/Index.vue` | Subscription details, current plan, usage meters |
| `resources/js/Tenant/Pages/Billing/Plans.vue` | Available plans with upgrade/downgrade CTAs |
| `resources/js/Tenant/Pages/Billing/Invoices.vue` | Invoice history table |

---

### 6.8 Seeders

| File | Purpose |
|---|---|
| `database/seeders/Central/PlanSeeder.php` | Already in Stage 2 — verify features JSON is complete |

---

### 6.9 Tests

| File | Covers |
|---|---|
| `tests/Unit/PlanFeaturesTest.php` | `planFeature()` returns correct value; returns default when key missing |
| `tests/Feature/Central/Billing/SubscribeTest.php` | Tenant subscribes to plan; duplicate active subscription rejected; trial period set |
| `tests/Feature/Central/Billing/ChangePlanTest.php` | Upgrade creates new subscription row; downgrade at period end; same plan rejected |
| `tests/Feature/Central/Billing/WebhookTest.php` | `invoice.paid` marks invoice paid, extends period; `invoice.payment_failed` sets `past_due`; invalid signature returns 400 |
| `tests/Feature/Tenant/Billing/PlanLimitTest.php` | Invite blocked when max_users reached; upload blocked when storage exceeded |

---

## Stage 7 — Admin Dashboard

**Goal:** Super admins have a full dashboard to monitor the platform, manage tenants/plans, view invoices, impersonate tenants, and observe Horizon queues.

**Estimated time:** 3–4 days

---

### 7.1 Controllers (Central — `app/Central/Controllers/Admin/`)

| File | Methods | Routes |
|---|---|---|
| `DashboardController.php` | `index()` | `GET /admin/dashboard` |
| `TenantController.php` | `index`, `show`, `update`, `suspend`, `activate`, `destroy` | `GET/PATCH/DELETE /admin/tenants`, `POST /admin/tenants/{id}/suspend|activate` |
| `PlanController.php` | `index`, `store`, `show`, `update` | `GET/POST /admin/plans`, `GET/PATCH /admin/plans/{id}` |
| `SubscriptionController.php` | `index` | `GET /admin/subscriptions` |
| `InvoiceController.php` | `index`, `show` | `GET /admin/invoices` |

---

### 7.2 API Resources (`app/Central/Resources/`)

| File | Exposes |
|---|---|
| `TenantResource.php` | id, name, slug, status, owner_email, plan, subscription_status, users_count, created_at |
| `PlanResource.php` | id, name, slug, price_monthly, price_yearly, features, is_active |
| `SubscriptionResource.php` | plan, status, billing_cycle, period dates, usage |
| `InvoiceResource.php` | number, status, amounts, currency, paid_at |

---

### 7.3 Services (`app/Central/Services/`)

| File | Methods | Purpose |
|---|---|---|
| `AdminDashboardService.php` | `kpis(): array` | Computes: total tenants, active tenants, MRR, trial count, recent signups (cached 15 min) |

---

### 7.4 Inertia Pages

| File | Purpose |
|---|---|
| `resources/js/Central/Pages/Admin/Dashboard.vue` | KPI cards, tenant growth chart, recent signups table |
| `resources/js/Central/Pages/Admin/Tenants/Index.vue` | Searchable/filterable tenant table with status badges |
| `resources/js/Central/Pages/Admin/Tenants/Show.vue` | Tenant profile: subscription, domains, user count, actions |
| `resources/js/Central/Pages/Admin/Plans/Index.vue` | Plan list with pricing |
| `resources/js/Central/Pages/Admin/Plans/Form.vue` | Create/edit plan form with feature limit fields |
| `resources/js/Central/Pages/Admin/Invoices/Index.vue` | Platform-wide invoice table with filters |

---

### 7.5 Horizon Protection

In `HorizonServiceProvider.php`:

```php
Horizon::auth(function (Request $request) {
    return Auth::guard('admin')->check();
});
```

Horizon dashboard available at `/horizon`.

---

### 7.6 Tests

| File | Covers |
|---|---|
| `tests/Feature/Central/Admin/TenantManagementTest.php` | Admin lists tenants; suspends tenant; activates tenant; non-admin returns 403 |
| `tests/Feature/Central/Admin/PlanManagementTest.php` | Admin creates plan; updates plan features; inactive plan hidden from public listing |
| `tests/Feature/Central/Admin/DashboardTest.php` | KPI endpoint returns expected keys; unauthenticated returns 401 |

---

## Stage 8 — Tenant Dashboard

**Goal:** Tenant users have a complete working dashboard: user management, settings, file uploads, and profile management. All protected by policy-based authorization.

**Estimated time:** 4–5 days

---

### 8.1 Controllers (Tenant — `app/Tenant/Controllers/`)

| File | Methods | Routes |
|---|---|---|
| `Dashboard/DashboardController.php` | `index()` | `GET /dashboard` |
| `Users/UserController.php` | `index`, `show`, `update`, `destroy` | `GET/PATCH/DELETE /users/{user}` |
| `Users/UserInvitationController.php` | `store()` | `POST /users/invite` |
| `Settings/SettingsController.php` | `index`, `update` | `GET/PATCH /settings/{group?}` |
| `Files/FileController.php` | `index`, `store`, `show`, `destroy` | `GET/POST /files`, `GET/DELETE /files/{file}` |
| `Files/StorageUsageController.php` | `show()` | `GET /files/storage-usage` |

---

### 8.2 Form Requests (`app/Tenant/Requests/`)

| File | Rules |
|---|---|
| `Users/InviteUserRequest.php` | `email` required email unique:users, `name` required, `roles` required array min:1, `roles.*` exists:roles,id |
| `Users/UpdateUserRequest.php` | `name` optional, `email` optional unique:users ignoring current, `status` optional enum |
| `Settings/UpdateSettingsRequest.php` | Dynamic validation per group (general: timezone, language; mail: driver, host, port, etc.) |
| `Files/UploadFileRequest.php` | `file` required mimes:jpg,png,pdf,docx,xlsx max:20480, `collection` nullable in list |

---

### 8.3 Services (`app/Tenant/Services/`)

| File | Methods | Purpose |
|---|---|---|
| `UserService.php` | `invite(array): User`, `activate(User): void`, `deactivate(User): void`, `remove(User): void` | User lifecycle; creates user record + dispatches invitation notification |
| `FileService.php` | `upload(UploadedFile, User, string $collection): File`, `delete(File): void`, `temporaryUrl(File): string`, `currentUsageBytes(int $tenantId): int` | S3 upload with tenant-prefixed path; quota check before upload |
| `SettingsService.php` | `get(string $group): array`, `update(string $group, array $data): void`, `flush(): void` | Reads/writes settings table; encrypts flagged keys; clears settings cache |

---

### 8.4 Notifications (`app/Tenant/Notifications/`)

| File | Channel | Trigger |
|---|---|---|
| `UserInvitedNotification.php` | `mail` | When `UserService::invite()` is called — contains signed accept URL |
| `UserWelcomeNotification.php` | `mail` | When invited user accepts and sets password |

---

### 8.5 Inertia Pages

| File | Purpose |
|---|---|
| `resources/js/Tenant/Pages/Dashboard.vue` | Welcome widget, usage meters, recent activity feed |
| `resources/js/Tenant/Pages/Users/Index.vue` | User table with role badges, invite button |
| `resources/js/Tenant/Pages/Users/Show.vue` | User profile, role assignment, status toggle |
| `resources/js/Tenant/Pages/Settings/General.vue` | Company name, timezone, language |
| `resources/js/Tenant/Pages/Settings/Mail.vue` | SMTP settings (only for owner role) |
| `resources/js/Tenant/Pages/Files/Index.vue` | File list with collection filter and storage usage bar |

---

### 8.6 Observers (`app/Tenant/Observers/`)

| File | Hooks | Purpose |
|---|---|---|
| `AuditObserver.php` | `created`, `updated`, `deleted`, `restored` | Writes to `audit_logs` automatically for any model using the `HasAuditLog` trait |

Register in `AppServiceProvider`:

```php
User::observe(AuditObserver::class);
Role::observe(AuditObserver::class);
```

---

### 8.7 Traits (`app/Support/Traits/`)

| File | Purpose |
|---|---|
| `HasAuditLog.php` | Boots `AuditObserver`; implements `getAuditableAttributes()` to exclude sensitive fields |
| `HasActivityLog.php` | `logActivity(string $event, string $description, array $properties = [])` helper method |

---

### 8.8 Tests

| File | Covers |
|---|---|
| `tests/Feature/Tenant/Users/InviteUserTest.php` | Invite creates user with `invited` status; notification sent; plan user limit enforced; duplicate email rejected |
| `tests/Feature/Tenant/Users/UpdateUserTest.php` | Admin updates user; user updates own profile; cannot update owner role |
| `tests/Feature/Tenant/Users/RemoveUserTest.php` | Admin removes user; cannot remove self; cannot remove owner |
| `tests/Feature/Tenant/Files/UploadFileTest.php` | File uploaded to correct S3 path; quota enforced; unsupported MIME rejected |
| `tests/Unit/StorageQuotaTest.php` | `currentUsageBytes()` sums file sizes correctly; quota check passes/fails |
| `tests/Feature/Tenant/AuditLogTest.php` | User creation writes audit log; user deletion writes audit log |

---

## Stage 9 — API Layer

**Goal:** A versioned REST API under `/api/v1` with Sanctum token auth, ability scopes, per-plan rate limiting, and full API Resources for all entities.

**Estimated time:** 3–4 days

---

### 9.1 Controllers (`app/Tenant/Controllers/Api/V1/`)

Mirror the web controllers but return JSON via API Resources:

| File | Resource returned | Notes |
|---|---|---|
| `AuthController.php` | token + UserResource | `login`, `logout`, `me` |
| `UserController.php` | UserResource collection | list, show, invite, update, remove |
| `RoleController.php` | RoleResource collection | CRUD + sync permissions |
| `FileController.php` | FileResource | upload, list, show (temporary URL), delete |
| `SettingsController.php` | SettingsResource | get by group, update |
| `ActivityLogController.php` | ActivityLogResource | list with filters |
| `AuditLogController.php` | AuditLogResource | list with filters |
| `NotificationController.php` | NotificationResource | list, mark-read, mark-all-read, delete |

#### Central API Controllers (`app/Central/Controllers/Api/V1/`)

| File | Resource returned | Notes |
|---|---|---|
| `Admin/TenantController.php` | TenantResource | Admin-only CRUD + status mutations |
| `Admin/PlanController.php` | PlanResource | Admin CRUD; public `index` unauthenticated |
| `Billing/SubscriptionController.php` | SubscriptionResource | Tenant owner only |
| `Billing/InvoiceController.php` | InvoiceResource | Tenant owner only |
| `Webhooks/StripeController.php` | raw response | No auth; validates Stripe-Signature |

---

### 9.2 API Resources (`app/Tenant/Resources/`)

| File | Fields |
|---|---|
| `UserResource.php` | id, name, email, avatar_url, is_owner, status, roles, permissions, last_login_at |
| `RoleResource.php` | id, name, display_name, description, is_system, permissions |
| `PermissionResource.php` | id, name, group |
| `FileResource.php` | id, original_name, mime_type, size_bytes, collection, temporary_url, created_at |
| `ActivityLogResource.php` | id, user_name, event, description, properties, ip_address, created_at |
| `AuditLogResource.php` | id, user_name, event, auditable_type, auditable_id, old_values, new_values, created_at |
| `NotificationResource.php` | id, type, data, read_at, created_at |

---

### 9.3 Rate Limiting (`app/Providers/AppServiceProvider.php`)

```php
RateLimiter::for('auth',       fn(Request $r) => Limit::perMinute(10)->by('ip:'.$r->ip()));
RateLimiter::for('tenant-api', fn(Request $r) => Limit::perMinute(
    tenant()->planFeature('api_calls_per_minute', 60)
)->by("tenant:".tenant()->id.":user:".$r->user()?->id));
RateLimiter::for('file-upload', fn(Request $r) => Limit::perMinute(20)->by("user:".$r->user()?->id));
```

---

### 9.4 Middleware

| File | Purpose |
|---|---|
| `SetApiRateLimit.php` | Applies correct named limiter based on route group |
| `SetSecurityHeaders.php` | Injects all security headers (X-Frame-Options, CSP, HSTS, etc.) |

---

### 9.5 Token Management

| File | Purpose |
|---|---|
| `app/Tenant/Controllers/Api/V1/TokenController.php` | `index` (list tokens), `store` (create with abilities), `destroy` (revoke) |
| `resources/js/Tenant/Pages/Profile/ApiTokens.vue` | Token management UI: list, create with scope selection, revoke |

---

### 9.6 Tests

| File | Covers |
|---|---|
| `tests/Feature/Tenant/API/ApiTokenTest.php` | Create token; token authenticates; revoked token rejected; scoped token cannot access out-of-scope endpoint |
| `tests/Feature/Tenant/API/RateLimitingTest.php` | Exceeding per-minute limit returns 429; headers present on every response; limit differs per plan |
| `tests/Feature/Central/Auth/TenantRegistrationTest.php` | `POST /api/v1/auth/register` creates tenant and returns 202; validation errors returned |

---

## Stage 10 — Notifications & Activity Logs

**Goal:** Every meaningful event generates an activity log entry and optionally a database notification. Audit logs are automatic. Real-time notification badge uses polling (or can be upgraded to WebSockets).

**Estimated time:** 2–3 days

---

### 10.1 Services (`app/Tenant/Services/`)

| File | Methods | Purpose |
|---|---|---|
| `ActivityLogger.php` | `log(string $event, string $description, ?Model $subject = null, array $properties = []): ActivityLog` | Writes to `activity_logs`; reads user + IP from request context |
| `NotificationService.php` | `send(User $user, Notification $notification): void`, `markAllRead(User $user): void` | Database notification wrapper; clears notification cache |

---

### 10.2 Notifications (additional, `app/Tenant/Notifications/`)

| File | Channel | Trigger |
|---|---|---|
| `NewUserJoinedNotification.php` | `database` | When invited user accepts invitation |
| `StorageLimitWarningNotification.php` | `database` + `mail` | When usage exceeds 80% of plan limit |
| `PlanUpgradedNotification.php` | `database` + `mail` | When subscription plan changes |
| `SubscriptionCancelledNotification.php` | `database` + `mail` | When cancellation confirmed |

---

### 10.3 Controllers — extend existing

Add to `app/Tenant/Controllers/Dashboard/DashboardController.php`:

- `recentActivity()` — returns last 20 activity log entries for the auth user, used by dashboard widget

---

### 10.4 Scheduled Commands

In `routes/console.php`:

```php
Schedule::command('activity-logs:prune')->daily();
Schedule::command('audit-logs:prune')->weekly();
```

| Command | Purpose |
|---|---|
| `app/Console/Commands/PruneActivityLogs.php` | Deletes logs older than `audit_logs_days` plan feature |
| `app/Console/Commands/PruneAuditLogs.php` | Deletes audit rows older than retention window |

---

### 10.5 Inertia Pages / Components

| File | Purpose |
|---|---|
| `resources/js/Tenant/Pages/ActivityLog.vue` | Full activity log with user filter, event filter, date range |
| `resources/js/Tenant/Pages/AuditLog.vue` | Audit trail with before/after diff viewer |
| `resources/js/Components/NotificationBell.vue` | Dropdown showing unread notifications with mark-as-read |

---

### 10.6 Tests

| File | Covers |
|---|---|
| `tests/Feature/Tenant/ActivityLogTest.php` | User invite written to activity log; file upload written to activity log; logs scoped to tenant |
| `tests/Feature/Tenant/AuditLogTest.php` | Model create/update/delete write audit rows with correct before/after values |
| `tests/Feature/Tenant/NotificationTest.php` | Notification stored in DB; mark-read updates `read_at`; mark-all-read clears all |

---

## Stage 11 — Testing

**Goal:** Reach ≥ 80% line coverage. Fill all remaining test gaps. Add architecture tests. Validate CI pipeline runs end-to-end.

**Estimated time:** 3–4 days

---

### 11.1 Complete Test Files

All test stubs created in prior stages are now fully implemented:

| Directory | Focus |
|---|---|
| `tests/Unit/` | Pure logic — resolver, quota, permissions, plan features |
| `tests/Feature/Central/` | All central HTTP endpoints; onboarding; billing; webhooks |
| `tests/Feature/Tenant/` | All tenant HTTP endpoints; isolation; auth; users; roles; files; API |
| `tests/Architecture/` | Namespace rules, no-`env()`, services not `new`-ed in controllers, jobs implement trait |

---

### 11.2 Isolation Test Suite

| File | Asserts |
|---|---|
| `tests/Feature/Tenant/TenantIsolationTest.php` | Tenant A user cannot read tenant B users; tenant A files not accessible from tenant B; activity logs isolated; settings isolated |

---

### 11.3 Pest Configuration Finalisation

Update `tests/Pest.php`:

```php
// Shared dataset for plan limits
dataset('plan_features', [
    'max_users'         => ['max_users', 5, 6],
    'storage_gb'        => ['storage_gb', 10, 11],
    'api_calls_per_day' => ['api_calls_per_day', 1000, 1001],
]);
```

---

### 11.4 Coverage & CI

```bash
# local coverage
php artisan test --compact --coverage --min=80

# parallel (faster in CI)
php artisan test --compact --parallel --coverage
```

Update `.github/workflows/ci.yml` to gate on `--min=80`.

---

### 11.5 Mutation Testing (optional, post-MVP)

```bash
composer require --dev infection/infection
vendor/bin/infection --min-msi=60 --min-covered-msi=70
```

---

## Stage 12 — Production Readiness

**Goal:** The application is hardened, optimised, documented, and deployable to production with zero-downtime releases.

**Estimated time:** 3–4 days

---

### 12.1 Security Hardening

| Task | File / Location |
|---|---|
| Security headers middleware | `app/Http/Middleware/SetSecurityHeaders.php` (register globally in `bootstrap/app.php`) |
| CSP nonce with Inertia | Add nonce to `app.blade.php`; pass to Inertia shared props; use in Vue script tags |
| CORS config audit | `config/cors.php` — tighten `allowed_origins` to exact domains |
| 2FA (TOTP) | Install `pragmarx/google2fa-laravel`; add `two_factor_secret` + `two_factor_recovery_codes` cols to tenant `users` table |
| Brute-force lockout | Middleware `ThrottleLogins` already applied; add `AccountLocked` notification |
| Larastan level 9 | `phpstan.neon` — fix all remaining errors before tagging |
| `vendor/bin/pint --dirty` | Run before every commit (add as pre-commit hook via Husky equivalent) |

---

### 12.2 Performance

| Task | Implementation |
|---|---|
| Route caching | `php artisan route:cache` in deploy script |
| Config caching | `php artisan config:cache` |
| View caching | `php artisan view:cache` |
| Event caching | `php artisan event:cache` |
| OPcache | Enabled in `docker/php/php-prod.ini` |
| Query optimisation | Add `->with([...])` eager loads verified by `barryvdh/laravel-debugbar` in dev |
| Redis pipelining | Batch permission cache writes at role-sync time |

---

### 12.3 Observability

| File | Purpose |
|---|---|
| `config/logging.php` | Add `stderr` channel with JSON formatter and `tenant_id`, `user_id` context injection |
| `app/Logging/TenantContextProcessor.php` | Monolog processor that appends tenant/user context to every log record |
| `config/sentry.php` (or `config/flare.php`) | Error tracking integration with `before_send` hook to attach tenant context |

---

### 12.4 Backup Scripts

| File | Purpose |
|---|---|
| `scripts/backup-central.sh` | `mysqldump landlord` → gzip → upload to S3 |
| `scripts/backup-tenant.sh` | Iterate all active tenants; `mysqldump tenant_{slug}` → gzip → S3 |
| `scripts/backup-redis.sh` | Trigger `BGSAVE`; copy `.rdb` → S3 |

---

### 12.5 Final Deployment Checklist

| File | Purpose |
|---|---|
| `docker-compose.yml` | Final production config (no mailpit, no minio) |
| `docker-compose.prod.yml` | Production overlay with resource limits, health checks |
| `.github/workflows/deploy.yml` | Staging (on `main`) + production (on `v*` tag) workflows |
| `deploy.sh` | Zero-downtime: pull → `composer install --no-dev` → `yarn build` → `migrate` → `cache:*` → `horizon:terminate` |

---

### 12.6 Developer Documentation (`README.md`)

Sections:

1. Architecture overview (link to `docs/SaaS-Architecture.md`)
2. Quickstart with Docker (`docker compose up`)
3. Creating a demo tenant (`php artisan db:seed`)
4. Running tests
5. Environment variable reference
6. Stripe test mode setup
7. Deployment guide (link to `docs/Deployment.md`)

---

### 12.7 Final Verification

```bash
# Full test suite with coverage
php artisan test --compact --coverage --min=80

# Static analysis (zero errors)
vendor/bin/phpstan analyse --level=9

# Code style (zero changes)
vendor/bin/pint --test

# Production build
yarn build

# Smoke test: register a new tenant end-to-end
curl -X POST https://app.com/api/v1/auth/register -d '{...}'
# → 202 Accepted; poll status URL; confirm tenant DB created; login at subddomain
```

---

## Stage Summary

| Stage | Focus | Key deliverable |
|---|---|---|
| 1 | Project setup | Docker + Vite + Inertia + CI running |
| 2 | Tenant infrastructure | Subdomain routing, DB switching, central schema |
| 3 | Authentication | Login, verify, reset for both central and tenant |
| 4 | Tenant onboarding | Registration → automated DB provisioning |
| 5 | Roles & permissions | Policy-based RBAC with cached permission checks |
| 6 | Subscription system | Plan lifecycle, invoicing, Stripe webhooks, limits |
| 7 | Admin dashboard | KPIs, tenant management, plan management |
| 8 | Tenant dashboard | Users, files, settings, audit trail |
| 9 | API layer | Versioned REST, Sanctum tokens, rate limiting |
| 10 | Notifications & logs | Activity log, audit log, DB notifications |
| 11 | Testing | ≥ 80% coverage, isolation tests, architecture tests |
| 12 | Production readiness | Security headers, OPcache, logging, deploy scripts |

---

*Last updated: 2026-03-01*
