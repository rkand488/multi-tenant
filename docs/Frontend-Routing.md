# Frontend Routing

> **Routing model:** Server-side Laravel routes → Inertia responses
> **Stack:** Laravel 12 · Inertia.js v2 · Ziggy (named routes in JavaScript)

---

## 1. Core Concept

There is **one routing layer**: Laravel. The browser never manages routes directly. Every URL change either:

1. Triggers a full-page PHP render on first visit (Inertia hydration), or
2. Fires an Inertia XHR that returns JSON `{ component, props, url, version }` — rendered client-side.

```
User clicks <Link href="/dashboard">
        │
        ▼
Inertia intercepts (no full reload)
        │
        ▼
XHR GET /dashboard  (X-Inertia: true header)
        │
        ▼
Laravel Route  →  Middleware  →  Controller
        │
        ▼
Inertia::render('Tenant/Dashboard', $props)
        │
        ▼
JSON response  →  Vue mounts Tenant/Dashboard.vue
```

---

## 2. Route File Organisation

```
routes/
├── web.php          # All web (Inertia) routes
├── api.php          # JSON API routes (webhooks, mobile, future)
└── console.php      # Artisan / scheduled commands
```

All dashboard routes live in `web.php`. There are no separate frontend route files.

---

## 3. Middleware Groups

### `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
        \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
    ]);

    $middleware->alias([
        'admin'         => \App\Http\Middleware\EnsureIsAdmin::class,
        'tenant.auth'   => \App\Http\Middleware\EnsureTenantContext::class,
        'tenant.member' => \App\Http\Middleware\EnsureTenantMember::class,
        'verified'      => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ]);
})
```

### `EnsureIsAdmin` — Super-Admin guard

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $request->user()?->is_super_admin) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
```

### `EnsureTenantContext` — Tenant guard

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Tenancy\TenantContext;

class EnsureTenantContext
{
    public function handle(Request $request, Closure $next): mixed
    {
        $tenant = app(TenantContext::class)->current();

        if (! $tenant) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
```

---

## 4. Route Definitions

### Authentication Routes

```php
// routes/web.php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\VerifyEmailController;

Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class, 'create'])->name('login');
    Route::post('/login',   [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register',[RegisterController::class, 'store'])->name('register.store');

    Route::get('/forgot-password',               [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password',              [PasswordResetController::class, 'email'])->name('password.email');
    Route::get('/reset-password/{token}',        [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password',               [PasswordResetController::class, 'update'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/verify-email',                  [VerifyEmailController::class, 'notice'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}',      [VerifyEmailController::class, 'verify'])->name('verification.verify');
    Route::post('/email/verification-notification',[VerifyEmailController::class, 'resend'])->name('verification.send');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
```

### Tenant Dashboard Routes

```php
// routes/web.php

Route::middleware(['auth', 'verified', 'tenant.auth', 'tenant.member'])
    ->prefix('dashboard')
    ->name('tenant.')
    ->group(function () {

        Route::get('/', App\Http\Controllers\Tenant\DashboardController::class)
            ->name('dashboard');

        // Users
        Route::resource('users', App\Http\Controllers\Tenant\UserController::class)
            ->except(['create', 'edit']);
        Route::post('users/invite', [App\Http\Controllers\Tenant\UserController::class, 'invite'])
            ->name('users.invite');

        // Roles & Permissions
        Route::resource('roles', App\Http\Controllers\Tenant\RoleController::class)
            ->except(['create', 'edit']);

        // Subscription
        Route::get('subscription',          [App\Http\Controllers\Tenant\SubscriptionController::class, 'index'])
            ->name('subscription.index');
        Route::post('subscription/change',  [App\Http\Controllers\Tenant\SubscriptionController::class, 'change'])
            ->name('subscription.change');
        Route::post('subscription/cancel',  [App\Http\Controllers\Tenant\SubscriptionController::class, 'cancel'])
            ->name('subscription.cancel');

        // Settings
        Route::get('settings',  [App\Http\Controllers\Tenant\SettingsController::class, 'index'])
            ->name('settings.index');
        Route::patch('settings',[App\Http\Controllers\Tenant\SettingsController::class, 'update'])
            ->name('settings.update');

        // Activity Log
        Route::get('activity-log', App\Http\Controllers\Tenant\ActivityLogController::class)
            ->name('activity-log.index');
    });
```

### Super-Admin Routes

```php
// routes/web.php

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', App\Http\Controllers\Admin\DashboardController::class)
            ->name('dashboard');

        // Tenants
        Route::resource('tenants', App\Http\Controllers\Admin\TenantController::class)
            ->except(['edit']);
        Route::post('tenants/{tenant}/suspend', [App\Http\Controllers\Admin\TenantController::class, 'suspend'])
            ->name('tenants.suspend');
        Route::post('tenants/{tenant}/restore', [App\Http\Controllers\Admin\TenantController::class, 'restore'])
            ->name('tenants.restore');

        // Plans
        Route::resource('plans', App\Http\Controllers\Admin\PlanController::class)
            ->except(['edit']);

        // Analytics
        Route::get('analytics', App\Http\Controllers\Admin\AnalyticsController::class)
            ->name('analytics.index');

        // Billing
        Route::get('billing', App\Http\Controllers\Admin\BillingController::class)
            ->name('billing.index');
    });
```

---

## 5. Controller Patterns

### Single-Action Invokable Controller

```php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $tenant = $request->user()->tenant;

        return Inertia::render('Tenant/Dashboard', [
            'stats' => [
                'user_count'        => $tenant->users()->count(),
                'active_sessions'   => $tenant->sessions()->active()->count(),
                'storage_used_mb'   => $tenant->storageUsedMb(),
            ],
            'recentActivity' => $tenant->activityLogs()
                ->with('causer')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }
}
```

### Resource Controller with Inertia

```php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = $request->user()->tenant
            ->users()
            ->with('roles')
            ->withLastLogin()
            ->when($request->search, fn ($q, $s) => $q->search($s))
            ->orderBy($request->sort ?? 'name', $request->direction ?? 'asc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Tenant/Users/Index', [
            'users'   => $users,
            'filters' => $request->only('search', 'sort', 'direction'),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        // Create user logic ...

        return redirect()->route('tenant.users.index')
            ->with('success', 'User invited successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return back()->with('success', 'User removed.');
    }
}
```

---

## 6. HandleInertiaRequests Middleware

This middleware runs on every web request and injects global props (shared data).

`app/Http/Middleware/HandleInertiaRequests.php`

```php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            'auth' => [
                'user' => $request->user()?->only(
                    'id', 'name', 'email', 'email_verified_at', 'avatar'
                ),
                'permissions' => $request->user()?->getAllPermissions()->pluck('name'),
                'roles'       => $request->user()?->getRoleNames(),
            ],

            'tenant' => $request->user()?->tenant?->only(
                'id', 'name', 'slug', 'logo', 'subscription_status'
            ),

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],

            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
```

---

## 7. Inertia Navigation in Vue

### `<Link>` Component

```vue
<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <!-- Standard navigation -->
    <Link :href="route('tenant.dashboard')">Dashboard</Link>

    <!-- Method override -->
    <Link :href="route('logout')" method="post" as="button" type="button">
        Sign out
    </Link>

    <!-- Preserve scroll position -->
    <Link :href="route('tenant.users.index')" preserve-scroll>Users</Link>

    <!-- Only reload certain props -->
    <Link :href="route('tenant.dashboard')" :only="['stats']">Refresh Stats</Link>
</template>
```

### `router` Programmatic Navigation

```javascript
import { router } from '@inertiajs/vue3';

// Simple GET
router.get(route('tenant.users.index'));

// POST with data
router.post(route('tenant.users.invite'), { email: 'user@example.com' });

// PATCH with callbacks
router.patch(route('tenant.settings.update'), form.value, {
    preserveScroll: true,
    onSuccess: () => toast.success('Settings saved.'),
    onError:   (errors) => console.error(errors),
});

// DELETE with confirmation
router.delete(route('tenant.users.destroy', user.id), {
    onBefore: () => confirm('Delete this user?'),
    onSuccess: () => toast.success('User deleted.'),
});
```

### Inertia `useForm` Helper

```javascript
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name:  '',
    email: '',
    role:  'member',
});

const submit = () => {
    form.post(route('tenant.users.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
```

---

## 8. Named Routes with Ziggy

Ziggy exposes all named Laravel routes as a JavaScript `route()` helper. It is bootstrapped in `app.js` via `.use(ZiggyVue)` and the `@routes` Blade directive.

```javascript
// Works in any Vue component or script
route('tenant.users.index')
// → /dashboard/users

route('tenant.users.show', { user: 42 })
// → /dashboard/users/42

route('admin.tenants.show', { tenant: 'acme' })
// → /admin/tenants/acme

// Check current route
import { usePage } from '@inertiajs/vue3';
// Or use Ziggy's route().current():
route().current('tenant.*')    // → true when on any tenant page
route().current('admin.*')     // → true when on any admin page
```

---

## 9. Partial Reloads

Avoid re-fetching all page props when only one section needs updating:

```javascript
// Refresh only the stats panel without a full page transition
router.reload({ only: ['stats'] });

// After a background polling tick
setInterval(() => {
    router.reload({ only: ['stats'], preserveScroll: true });
}, 30_000);
```

Use `only` on `<Link>` as well:

```vue
<Link :href="route('tenant.dashboard')" :only="['stats']" preserve-scroll>
    Refresh
</Link>
```

---

## 10. Lazy (Deferred) Props

For expensive data that should not block the initial render, use Inertia's deferred props (Inertia v2 feature):

```php
return Inertia::render('Admin/Analytics/Index', [
    // Fast — included in initial response
    'period' => $request->period ?? '30d',

    // Deferred — fetched in a second XHR after paint
    'chartData'    => Inertia::defer(fn () => $this->buildChartData($period)),
    'topTenants'   => Inertia::defer(fn () => $this->topTenantsByRevenue()),
]);
```

In the Vue page, use `v-if` or a skeleton while deferred props resolve:

```vue
<template>
    <div>
        <StatsCard v-if="chartData" :data="chartData" />
        <div v-else class="animate-pulse h-64 rounded-card bg-gray-100" />
    </div>
</template>
```

---

## 11. Error Handling

### 404 / 403 Pages

Register custom Inertia error pages in `bootstrap/app.php`:

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
        if ($request->header('X-Inertia')) {
            return Inertia::render('Error', [
                'status'  => $e->getStatusCode(),
                'message' => $e->getMessage() ?: Response::$statusTexts[$e->getStatusCode()] ?? 'Unknown error',
            ])->toResponse($request)->setStatusCode($e->getStatusCode());
        }
    });
})
```

`resources/js/Pages/Error.vue`:

```vue
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineOptions({ layout: GuestLayout });

const props = defineProps({
    status:  { type: Number, required: true },
    message: { type: String, default: 'An error occurred.' },
});

const tip = {
    403: 'You do not have permission to access this page.',
    404: 'The page you are looking for does not exist.',
    500: 'Something went wrong on our end. Please try again.',
}[props.status] ?? props.message;
</script>

<template>
    <div class="text-center">
        <p class="text-7xl font-extrabold text-brand-600">{{ status }}</p>
        <p class="mt-3 text-xl font-semibold text-gray-900 dark:text-white">{{ message }}</p>
        <p class="mt-2 text-sm text-gray-500">{{ tip }}</p>
        <Link :href="route('tenant.dashboard')" class="mt-6 inline-block text-brand-600 hover:underline">
            Go back home
        </Link>
    </div>
</template>
```

---

## 12. Route Summary Table

| Name | Method | URI | Middleware | Inertia Page |
|---|---|---|---|---|
| `login` | GET | `/login` | `guest` | `Auth/Login` |
| `register` | GET | `/register` | `guest` | `Auth/Register` |
| `password.request` | GET | `/forgot-password` | `guest` | `Auth/ForgotPassword` |
| `password.reset` | GET | `/reset-password/{token}` | `guest` | `Auth/ResetPassword` |
| `verification.notice` | GET | `/verify-email` | `auth` | `Auth/VerifyEmail` |
| `tenant.dashboard` | GET | `/dashboard` | `auth,verified,tenant.auth,tenant.member` | `Tenant/Dashboard` |
| `tenant.users.index` | GET | `/dashboard/users` | same | `Tenant/Users/Index` |
| `tenant.users.show` | GET | `/dashboard/users/{user}` | same | `Tenant/Users/Show` |
| `tenant.roles.index` | GET | `/dashboard/roles` | same | `Tenant/Roles/Index` |
| `tenant.subscription.index` | GET | `/dashboard/subscription` | same | `Tenant/Subscription/Index` |
| `tenant.settings.index` | GET | `/dashboard/settings` | same | `Tenant/Settings/Index` |
| `tenant.activity-log.index` | GET | `/dashboard/activity-log` | same | `Tenant/ActivityLog/Index` |
| `admin.dashboard` | GET | `/admin` | `auth,admin` | `Admin/Dashboard` |
| `admin.tenants.index` | GET | `/admin/tenants` | same | `Admin/Tenants/Index` |
| `admin.tenants.show` | GET | `/admin/tenants/{tenant}` | same | `Admin/Tenants/Show` |
| `admin.plans.index` | GET | `/admin/plans` | same | `Admin/Plans/Index` |
| `admin.analytics.index` | GET | `/admin/analytics` | same | `Admin/Analytics/Index` |
| `admin.billing.index` | GET | `/admin/billing` | same | `Admin/Billing/Index` |

---

## Related Documentation

- [Frontend-Architecture.md](Frontend-Architecture.md) — Folder structure, Inertia bootstrap
- [UI-Components.md](UI-Components.md) — Component library reference
- [State-Management.md](State-Management.md) — Pinia stores used in pages
