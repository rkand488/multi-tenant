# Tenant Login & Working Process — Gap Analysis & Improvement Suggestions

> **Reviewed:** 2026-03-04  
> **Scope:** Tenant registration, login, database provisioning, user workflows, owner flows

---

## Executive Summary

The project has a well-designed API and middleware layer, but the actual **tenant runtime process is not wired end-to-end**. The architecture promises database-per-tenant isolation, but the current code stores everything in the central database with `tenant_id` scoping — the exact anti-pattern the design docs warn against. Several critical pieces (database provisioning, invitation emails, password reset, subdomain enforcement) are stubbed or missing entirely.

---

## 1. Architecture Mismatch — The Most Critical Issue

### What the docs promise
The `SaaS-Architecture.md` and `MultiTenant-Strategy.md` explicitly define a **database-per-tenant** model where each tenant gets an isolated MySQL database (`tenant_{slug}`).

### What actually exists
All tenant data is stored in the **central database** with `tenant_id` foreign keys:

- `users` table → central DB, with `tenant_id` column  
- `roles` table → central DB, with `tenant_id` column  
- `invitations` table → central DB, with `tenant_id` column  
- `activity_logs` table → central DB, with `tenant_id` column  
- `tenant_files` table → central DB, with `tenant_id` column  

The `DashboardController`, `UserController`, and other web controllers all query `User::where('tenant_id', $user->tenant_id)` directly — bypassing `TenantContext` entirely and using the single-DB + `tenant_id` pattern.

### Suggested path

**Option A (Recommended short-term):** Commit to the single-DB approach for now, accept the trade-off, remove the misleading architecture docs about per-DB isolation, and add global Eloquent scopes to prevent data leakage.

**Option B (Full implementation):** Properly implement the database-per-tenant architecture:
1. Move users, roles, permissions, activity_logs, files into `database/migrations/tenant/`
2. Apply `IdentifyTenant` middleware to all web dashboard routes
3. Implement the `ProvisionTenantDatabase` job (see Section 2)
4. Remove `tenant_id` from central users table; users live in their tenant's DB only

---

## 2. Missing: Tenant Database Provisioning

### Current state
`TenantRegistrationService::register()` creates the `Tenant` record and immediately sets status to `Active` with a TODO comment:

```php
// Transition tenant to Active immediately (provisioning logic can
// be extended here to queue DB creation, etc.).
$tenant->update(['status' => TenantStatus::Active]);
```

No database is ever created. If the application ever calls `DatabaseManager::connectTenant()`, it would fail with a "database not found" error.

### What needs to be built

**`app/Central/Jobs/ProvisionTenantDatabase.php`** — queued job that:
1. Creates the tenant database: `CREATE DATABASE IF NOT EXISTS tenant_{slug}`
2. Runs tenant-specific migrations against the new database
3. Creates the owner user record **in the tenant's database** (not central)
4. Updates tenant status to `Active`
5. Dispatches `SendTenantWelcomeEmail`

**`database/migrations/tenant/`** — a new folder for tenant-specific schema:
```
database/migrations/tenant/
  0001_create_users_table.php       ← users live in tenant DB
  0002_create_roles_table.php
  0003_create_permissions_table.php
  0004_create_activity_logs_table.php
  0005_create_files_table.php
  0006_create_team_settings_table.php
```

**Updated `TenantRegistrationService`:**
```php
// Keep tenant in Provisioning status
$tenant->update(['status' => TenantStatus::Provisioning]);

// Dispatch async provisioning job
ProvisionTenantDatabase::dispatch($tenant, $data);

return ['tenant' => $tenant, 'user' => null]; // user created in job
```

### Artisan command
The existing `MigrateTenants` command is well-built but needs a `--path` option pointing to `database/migrations/tenant/`. Currently it would run the central migrations against tenant databases.

Fix in `MigrateTenants::handle()`:
```php
Artisan::call($command, array_merge($options, [
    '--database' => 'tenant',
    '--path'     => 'database/migrations/tenant',
]), $this->output);
```

---

## 3. Missing: `IdentifyTenant` Middleware on Web Routes

### Current state
The web dashboard routes only use `auth` and `tenant_or_super_admin` middleware:

```php
// web.php
Route::middleware(['auth', 'tenant_or_super_admin'])->prefix('dashboard')->group(...)
```

The `IdentifyTenant` middleware is **never applied to web routes**. This means:
- `TenantContext::get()` would throw `RuntimeException` in any controller that calls it
- `DatabaseManager::connectTenant()` is never called for web requests
- Any controller that uses `TenantContext` directly would crash

### Current workaround
Controllers bypass `TenantContext` by reading `$user->tenant_id` directly:
```php
// DashboardController.php — bypasses TenantContext entirely
$tenant = Tenant::on('central')->with('currentSubscription.plan')->find($user->tenant_id);
```

### Suggested fix
Apply tenant middleware to all tenant-facing web routes:

```php
// web.php
Route::middleware(['auth', 'tenant.optional', 'tenant_or_super_admin'])
    ->prefix('dashboard')
    ->name('tenant.')
    ->group(function () {
        // ...
    });
```

Then refactor controllers to use `TenantContext` rather than direct `tenant_id` queries.

---

## 4. Missing: Post-Registration Redirect to Tenant Subdomain

### Current state
After registration, the user is redirected to `/dashboard` on whatever domain they registered from (likely the central domain):

```php
// WebAuthController::register()
return redirect()->route('tenant.dashboard');
```

If registration happens on `app.tenantrix.test` (central), the user lands at `app.tenantrix.test/dashboard`. The `IdentifyTenant` middleware won't resolve a tenant here, so the dashboard will either crash or show an empty/generic view.

### Suggested fix
After registration, redirect the owner to their subdomain:

```php
public function register(RegisterTenantRequest $request): RedirectResponse
{
    $result = $this->registrationService->register([...]);

    Auth::login($result['user']);
    $request->session()->regenerate();

    // Redirect to the tenant's actual subdomain
    $subdomain = $result['tenant']->slug . '.' . config('tenancy.domain');
    return redirect('https://' . $subdomain . '/dashboard');
}
```

For local development this requires the local DNS / `/etc/hosts` to resolve `{slug}.tenantrix.test`.

---

## 5. Missing: Email Notifications

### Invitation emails not sent
`InvitationService::invite()` creates an `Invitation` record but **sends no email**. The invited person has no way to know they were invited unless the token URL is shared manually.

**What needs to be built:**
- `app/Notifications/TenantInvitationNotification.php`
- Call `Notification::send()` (or `$invitation->notify()`) after creating the invitation
- Email should contain the accept URL: `/invitations/{token}/accept`

### Welcome email not sent
`ProvisionTenantDatabase` (when built) should dispatch:
- `app/Notifications/TenantWelcomeNotification.php` — sent to the owner once the workspace is ready
- Include the subdomain URL, getting-started guide link

### Password reset not implemented
The `/forgot-password` route renders an Inertia page but has no handler. `HomeController::forgotPassword()` just returns the view — nothing processes the reset request.

**What needs to be built:**
- `POST /forgot-password` → `PasswordResetController::sendLink()` using `Password::sendResetLink()`
- `GET /reset-password/{token}` → show reset form
- `POST /reset-password` → `PasswordResetController::reset()`
- Add `ResetPassword` notification

---

## 6. Missing: Role & Permission Enforcement in Web Controllers

### Current state
The web `UserController::store()` currently just returns `back()`:
```php
public function store(): RedirectResponse
{
    return back(); // TODO: implement
}
```

The `RoleController` stores roles but there is no system that checks a user's role/permissions before accessing sensitive actions. The API controllers use `$this->authorize(...)` via Laravel policies, but the web controllers largely skip authorization checks.

### Suggested improvements

1. **Implement `UserController::store()`** — should dispatch the invitation flow (or directly create the user if owner), not return `back()`
2. **Add policy checks to all web controllers** — mirror the API controllers' `$this->authorize()` calls
3. **Connect custom roles to permissions** — the `RolePermissionService` exists in the API but the web `RoleController` doesn't validate permission names against an allowed list
4. **Middleware for owner-only actions** — profile/team deletion, billing changes should be gated to `TenantOwner` role only

---

## 7. Missing: Email Verification

### Current state
Users created via invitation have `email_verified_at = null`. The `UserController` (web) shows their status as `'invited'` vs `'active'` based on `email_verified_at`:

```php
'status' => $u->email_verified_at ? 'active' : 'invited',
```

But there is no verification email sent and no route to verify an email address.

### Suggested fix
1. Implement `MustVerifyEmail` on the `User` model
2. Add email verification routes (Laravel's built-in `Auth::routes(['verify' => true])` pattern)
3. Add `verified` middleware to sensitive routes (billing, settings changes)
4. When `InvitationService::accept()` creates the user, auto-mark their email as verified (since they clicked through the invitation link — the email is already trusted)

---

## 8. Missing: Tenant Owner — Profile & Team Management

### What's stubbed
`SettingsController` (web) has routes but the implementation is incomplete:

- `PUT /dashboard/settings/profile` → `updateProfile()` — stub only  
- `PUT /dashboard/settings/password` → `updatePassword()` — stub only  
- `PUT /dashboard/settings/team` → `updateTeam()` — stub only  
- `DELETE /dashboard/settings/team` → `destroyTeam()` — cancels subscription but no cascade delete of users or data

### What the owner should be able to do (not yet implemented)

| Action | Route | Status |
|--------|-------|--------|
| Update own name/email | `PUT /settings/profile` | Not implemented |
| Change own password | `PUT /settings/password` | Not implemented |
| Update workspace name/slug | `PUT /settings/team` | Not implemented |
| Add a custom domain | `POST /settings/domains` | Route doesn't exist |
| Verify custom domain (DNS check) | `POST /settings/domains/{id}/verify` | Route doesn't exist |
| Delete/cancel workspace | `DELETE /settings/team` | Partial (no cascade) |
| View/manage API tokens | `GET /settings/tokens` | Route doesn't exist |
| Transfer ownership | `POST /settings/transfer-owner` | Route doesn't exist |

---

## 9. Missing: Billing Owner Flow

### What's there
- `SubscriptionController` (API) — full CRUD for subscriptions
- `BillingController` (web) — index, cancel, upgrade
- Plans API is public and functional

### What's missing for a real owner experience

1. **`POST /billing/upgrade` has no implementation** — `BillingController::upgrade()` needs to call `SubscriptionService::changePlan()`
2. **No Stripe checkout flow** — `SubscriptionService` exists but there is no payment method collection step. Free plan → paid plan needs a Stripe payment intent / checkout session
3. **No invoice PDF download** — invoices are stored but PDF generation is not implemented
4. **No dunning / failed payment handling** — no webhook listener for `invoice.payment_failed`
5. **Billing portal** — no link to Stripe Customer Portal for self-serve card updates
6. **Trial expiry** — `trial_ends_at` exists on the Tenant model but no job checks for expiry and downgrades/suspends the tenant

---

## 10. Missing: Tenant Isolation Safety Nets

Since data currently lives in the central DB with `tenant_id` scoping, a missing `where('tenant_id', ...)` clause would expose another tenant's data.

### Suggested fix: Global Eloquent Scopes
Until the per-DB architecture is implemented, add a `TenantScope` global scope to all tenant-scoped models:

```php
// app/Tenancy/Scopes/TenantScope.php
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (app(TenantContext::class)->check()) {
            $builder->where('tenant_id', app(TenantContext::class)->get()->id);
        }
    }
}
```

Apply via `ScopedByTenant` trait to `User`, `Role`, `Invitation`, `ActivityLog`, `TenantFile`.

This prevents accidental data leakage and removes repetitive `->where('tenant_id', ...)` from every query.

---

## 11. Missing: Tests

The test suite (`tests/Feature/`, `tests/Unit/`) currently has no tenant-specific tests. The `Project-Roadmap.md` marks tenant isolation tests as **Critical** but none exist.

### Suggested test coverage gaps

```
tests/Feature/
  Auth/
    TenantRegistrationTest.php    ← register, slug taken, DB provisioned
    TenantLoginTest.php           ← login on tenant domain, cross-tenant blocked
    InvitationTest.php            ← invite, accept, expired, already used
    PasswordResetTest.php         ← request link, reset
  Tenant/
    TenantIsolationTest.php       ← User A cannot access Tenant B's data
    UserManagementTest.php        ← CRUD, owner-only gate
    RolePermissionTest.php        ← custom roles, permission checks
    BillingTest.php               ← subscribe, upgrade, cancel
    SettingsTest.php              ← profile update, team update
  Admin/
    TenantManagementTest.php      ← suspend, activate, view
```

---

## Prioritised Action Plan

| Priority | Task | Effort |
|----------|------|--------|
| 🔴 Critical | Decide: commit to single-DB or implement per-DB provisioning | High |
| 🔴 Critical | Create `database/migrations/tenant/` + `ProvisionTenantDatabase` job | High |
| 🔴 Critical | Add `tenant.optional`/`tenant` middleware to web dashboard routes | Low |
| 🔴 Critical | Send invitation email via `TenantInvitationNotification` | Medium |
| 🔴 Critical | Implement `UserController::store()` (invite/create flow) | Medium |
| 🔴 Critical | Write tenant isolation tests | High |
| 🟠 High | Password reset routes + `PasswordResetController` | Medium |
| 🟠 High | Email verification for invited users | Medium |
| 🟠 High | Add global `TenantScope` to all scoped models | Low |
| 🟠 High | Owner settings: profile, password, team name update | Medium |
| 🟠 High | Post-registration redirect to tenant subdomain | Low |
| 🟠 High | Billing: implement upgrade/downgrade, trial expiry job | High |
| 🟡 Medium | Custom domain management (add, verify DNS) | Medium |
| 🟡 Medium | API token management UI | Medium |
| 🟡 Medium | Invoice PDF download | Low |
| 🟡 Medium | Ownership transfer | Low |
| 🟡 Medium | Welcome email after provisioning | Low |
