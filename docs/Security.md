# Security

> Security is enforced at every layer: network, application, database, and API.

---

## 1. Tenant Isolation

### Database Isolation

Each tenant operates in a **completely separate MySQL database**. The tenant database connection is initialised per-request by `IdentifyTenant` middleware and torn down in its `terminate()` method. There is no shared table and no `tenant_id` filter to forget — a query simply cannot reach another tenant's data through application code.

**Defence in depth:**

- MySQL users can be scoped per-tenant database (`GRANT ALL ON tenant_acme.* TO 'tenant_acme'@'%'`) for enterprise isolation.
- Central models are queried only through the `central` connection; an exception is thrown if tenant context attempts to use it.

### File Storage Isolation

S3 keys are namespaced: `/tenants/{tenantId}/...`. IAM bucket policies or signed URL generation limits each tenant to its own prefix. Attempting to access another prefix returns `403 Forbidden` from S3.

### Cache Isolation

All tenant cache keys are prefixed with `tenant:{id}:`. The `tenantCache()` helper enforces this. A Redis ACL (Laravel 12+) can restrict keys by prefix per connection.

### Queue Isolation

Tenant jobs carry the tenant ID and reconnect to the correct database inside `handle()`. Queue names are `tenant-{id}` so Horizon monitors and metrics are tenant-scoped.

---

## 2. Authentication

### Session-based (Web / Inertia)

- Laravel session guard with encrypted, HTTP-only cookies.
- CSRF tokens enforced on all state-changing requests via `VerifyCsrfToken` middleware.
- Session regeneration on login to prevent session fixation.

### Token-based (API)

- Laravel Sanctum personal access tokens.
- Tokens are hashed (SHA-256) before storage — the plain token is **never stored**.
- Tokens can carry ability scopes: `['read', 'write']`, `['admin']`.
- Token expiry: configurable per plan (default 90 days).
- Device name required on token creation for auditability.

### Email Verification

- Users must verify their email before accessing the tenant dashboard.
- Verification URL is signed and expires in 60 minutes.
- Resend throttled at 3 per hour per email address.

### Password Policy

- Minimum 12 characters.
- Must include uppercase, lowercase, numeric, and special characters.
- Validated via Laravel's `Password::min(12)->mixedCase()->numbers()->symbols()` rule.
- Breached password check (HaveIBeenPwned API) on registration and reset.

### Two-Factor Authentication (2FA)

- TOTP via `pragmarx/google2fa-laravel`.
- QR code enrollment in user profile settings.
- Recovery codes generated at enrollment (10 codes, single-use).
- 2FA enforcement per role (configurable by tenant admin).

---

## 3. Authorization Strategy

### Gate / Policy model

All authorisation uses Laravel Policies. Gates are registered in `AuthServiceProvider`:

```php
Gate::define('viewDashboard', fn(User $user) => $user->hasPermission('dashboard.view'));
```

Every controller checks permissions:

```php
$this->authorize('create', User::class);
// or
Gate::authorize('users.create');
```

### Permission Design

Permissions follow `resource.action` dot notation:

```
users.view       users.create    users.edit      users.delete     users.invite
roles.view       roles.create    roles.edit      roles.delete
files.view       files.upload    files.delete
billing.view     billing.manage
settings.view    settings.edit
audit.view
```

### Role Inheritance

Roles are flat (no hierarchy in the DB) but `owner` and `admin` are seeded with all permissions. The permission check is:

```php
public function hasPermission(string $permission): bool
{
    return cache()->remember(
        "tenant:".tenant()->id.":user:{$this->id}:perms",
        now()->addMinutes(30),
        fn() => $this->roles()
                     ->with('permissions')
                     ->get()
                     ->flatMap->permissions
                     ->pluck('name')
                     ->unique()
                     ->contains($permission)
    );
}
```

### Super Admin

Super admins bypass all tenant gates via a Gate::before hook. They are authenticated against the `super_admins` table using a separate `admin` guard.

```php
Gate::before(fn($user) => $user instanceof SuperAdmin ? true : null);
```

---

## 4. API Security

### HTTPS Everywhere

- Wildcard TLS certificate (`*.app.com`) via Let's Encrypt or ZeroSSL.
- HSTS header: `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`
- HTTP → HTTPS redirect enforced at Nginx level.

### Security Headers (Middleware `SetSecurityHeaders`)

```
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-{nonce}'; ...
Permissions-Policy: geolocation=(), microphone=(), camera=()
```

### CORS

Configured in `config/cors.php`:

```php
'allowed_origins' => [
    'https://app.com',
    'https://*.app.com',
],
'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
'supports_credentials' => true,
```

API routes for external consumers use strict origin validation.

### Input Validation

All incoming data passes through dedicated Form Request classes. Raw `$request->all()` is never directly passed to models. Mass assignment is guarded by `$fillable` arrays on every Eloquent model.

### SQL Injection Prevention

- All queries use Eloquent or the Query Builder with parameterised bindings.
- Raw SQL (`DB::statement`, `DB::select`) is forbidden unless reviewed and explicitly approved.
- Static analysis tool (Larastan level 9) flags unsafe query construction.

---

## 5. Rate Limiting

Defined in `RouteServiceProvider`:

```php
RateLimiter::for('api', function (Request $request) {
    return [
        Limit::perMinute(10)->by('auth:'.$request->ip())->response(
            fn() => response()->json(['message' => 'Too many attempts.'], 429)
        ),
    ];
});

RateLimiter::for('tenant-api', function (Request $request) {
    $tenant = tenant();
    $user   = $request->user();
    $limit  = $tenant->plan->features['api_calls_per_minute'] ?? 60;

    return Limit::perMinute($limit)
        ->by("tenant:{$tenant->id}:user:{$user?->id}:{$request->ip()}");
});
```

Redis-backed counters ensure rate limits are accurate across multiple PHP-FPM workers.

---

## 6. Secrets & Secrets Management

| Secret | Storage | Rotation |
|---|---|---|
| Database password | `.env` / Docker secret | Per environment |
| Stripe secret key | `.env` / Vault | On compromise |
| Sanctum token HMAC | `APP_KEY` | Rotated with `key:rotate` |
| 2FA seeds | Encrypted in tenant DB | Never (per-user) |
| S3 credentials | `.env` / IAM role | Per environment / IAM |

- **Never commit `.env`** — `.gitignore` enforced.
- CI/CD uses GitHub Actions Secrets → injected at deploy time.
- Production uses AWS Secrets Manager (or HashiCorp Vault) with Laravel's encrypted config support.

---

## 7. Webhook Security

Stripe webhook signature verification:

```php
$payload   = $request->getContent();
$sigHeader = $request->header('Stripe-Signature');

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, config('services.stripe.webhook_secret'));
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    return response('Invalid signature.', 400);
}
```

Replay attacks mitigated: Stripe includes a timestamp in the signature; events older than 5 minutes are rejected automatically.

---

## 8. Audit & Intrusion Detection

- **Activity logs** track every user action with IP + user agent.
- **Audit logs** track every model mutation with before/after values.
- **Failed login attempts** are recorded; after 5 attempts in 15 minutes → account lockout notification.
- **Suspicious IP detection:** login from new country triggers email notification.
- Rate limit breaches are logged to a dedicated Redis stream for monitoring.

---

*Last updated: 2026-03-01*
