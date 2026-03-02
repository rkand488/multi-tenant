<?php

use App\Central\Enums\SubscriptionStatus;
use App\Central\Models\Invoice;
use App\Central\Models\Plan;
use App\Central\Models\Subscription;
use App\Central\Models\Tenant;
use App\Http\Controllers\Auth\WebAuthController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Marketing landing page
Route::get('/', function () {
    return Inertia::render('Marketing/Home');
})->name('home');

// ── Demo / screenshot pages ────────────────────────────────────────────────
Route::prefix('demo')->name('demo.')->group(function () {
    Route::get('/admin', fn () => Inertia::render('Demo/AdminDashboardDemo'))->name('admin');
    Route::get('/tenant', fn () => Inertia::render('Demo/TenantDashboardDemo'))->name('tenant');
    Route::get('/analytics', fn () => Inertia::render('Demo/AnalyticsDemo'))->name('analytics');
    Route::get('/billing', fn () => Inertia::render('Demo/BillingDemo'))->name('billing');
});

Route::get('/dashboard', function () {
    /** @var \App\Models\User $user */
    $user = auth()->user();

    $tenant = $user->tenant_id
        ? Tenant::on('central')->with('currentSubscription.plan')->find($user->tenant_id)
        : null;

    $subscription = $tenant?->currentSubscription;
    $plan = $subscription?->plan;
    $planFeatures = $plan?->features ?? [];
    $maxUsers = $planFeatures['max_users'] ?? 5;
    $storageLimit = ($planFeatures['storage_gb'] ?? 5) * 1024; // MB

    $userCount = $user->tenant_id
        ? User::where('tenant_id', $user->tenant_id)->count()
        : 0;

    return Inertia::render('Tenant/Dashboard', [
        'stats' => [
            'user_count' => $userCount,
            'user_limit' => $maxUsers,
            'storage_used_mb' => 0,
            'storage_limit_mb' => $storageLimit,
            'active_sessions' => 0,
            'subscription_status' => $subscription?->status?->value ?? 'none',
        ],
        'plan' => $plan ? ['name' => $plan->name, 'interval' => $subscription->billing_interval->value] : null,
        'recentActivity' => [],
        'invitationsPending' => 0,
    ]);
})->middleware('auth')->name('tenant.dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);

    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);

    Route::get('/forgot-password', fn () => Inertia::render('Auth/Login'))->name('password.request');
});

Route::post('/logout', [WebAuthController::class, 'logout'])->middleware('auth')->name('logout');

// Tenant routes
Route::middleware('auth')->prefix('dashboard')->name('tenant.')->group(function () {
    // Users / team members
    Route::get('users', function () {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $query = $user->tenant_id
            ? User::where('tenant_id', $user->tenant_id)
            : User::whereNull('tenant_id');

        $users = $query->orderBy('name')->paginate(20);

        return Inertia::render('Tenant/Users/Index', [
            'users' => $users->through(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role_id' => $u->role?->value ?? null,
                'role_name' => $u->role?->label() ?? 'Member',
                'status' => $u->email_verified_at ? 'active' : 'invited',
                'joined_at' => $u->created_at?->toFormattedDateString(),
                'last_seen_at' => null,
            ]),
            'roles' => [],
            'filters' => [],
            'canInvite' => true,
        ]);
    })->name('users.index');

    Route::get('users/create', function () {
        /** @var \App\Models\User $user */
        $u = auth()->user();

        $tenant = $u->tenant_id ? Tenant::on('central')->with('currentSubscription.plan')->find($u->tenant_id) : null;
        $planFeatures = $tenant?->currentSubscription?->plan?->features ?? [];
        $maxUsers = $planFeatures['max_users'] ?? 5;
        $current = $u->tenant_id ? User::where('tenant_id', $u->tenant_id)->count() : 0;

        return Inertia::render('Tenant/Users/Create', [
            'roles' => [],
            'defaultRoleId' => null,
            'canInvite' => $maxUsers < 0 || $current < $maxUsers,
            'slotsRemaining' => $maxUsers < 0 ? null : max(0, $maxUsers - $current),
        ]);
    })->name('users.create');

    Route::post('users', fn () => back())->name('users.store');
    Route::patch('users/{user}', fn ($user) => back())->name('users.update');
    Route::delete('users/{user}', fn ($user) => back())->name('users.destroy');
    Route::post('users/{user}/resend-invite', fn ($user) => back())->name('users.resend-invite');

    // Roles
    Route::get('roles', function () {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $roles = $user->tenant_id
            ? \App\Models\Role::where('tenant_id', $user->tenant_id)
                ->orderBy('name')
                ->get()
                ->map(fn ($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $role->description,
                    'permissions' => $role->permissions ?? [],
                    'users_count' => 0,
                ])
            : collect();

        $availablePermissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'settings.view',
            'settings.update',
            'billing.view',
            'billing.manage',
            'files.view',
            'files.upload',
            'files.delete',
        ];

        return Inertia::render('Tenant/Roles/Index', [
            'roles' => $roles,
            'availablePermissions' => $availablePermissions,
        ]);
    })->name('roles.index');

    Route::post('roles', function (\Illuminate\Http\Request $request) {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        \App\Models\Role::create([
            'tenant_id' => $user->tenant_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return back()->with('success', 'Role created successfully.');
    })->name('roles.store');

    Route::put('roles/{role}', function (\Illuminate\Http\Request $request, \App\Models\Role $role) {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($role->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return back()->with('success', 'Role updated successfully.');
    })->name('roles.update');

    Route::delete('roles/{role}', function (\App\Models\Role $role) {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($role->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    })->name('roles.destroy');

    // Settings
    Route::get('settings', fn () => redirect()->route('tenant.settings.profile'))->name('settings.index');

    Route::get('settings/profile', function () {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return Inertia::render('Tenant/Settings/Profile', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    })->name('settings.profile');

    Route::get('settings/team', function () {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $tenant = $user->tenant_id
            ? Tenant::on('central')->find($user->tenant_id)
            : null;

        return Inertia::render('Tenant/Settings/Team', [
            'tenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'created_at' => $tenant->created_at?->toDateString(),
            ] : null,
            'timezones' => \DateTimeZone::listIdentifiers(),
        ]);
    })->name('settings.team');

    Route::put('settings/profile', fn () => back())->name('settings.update-profile');
    Route::put('settings/password', fn () => back())->name('settings.update-password');
    Route::put('settings/team', fn () => back())->name('settings.update-team');
    Route::delete('settings/team', fn () => redirect('/'))->name('settings.destroy');

    // Billing
    Route::get('billing', function () {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $tenant = $user->tenant_id
            ? Tenant::on('central')->with('currentSubscription.plan')->find($user->tenant_id)
            : null;

        $subscription = $tenant?->currentSubscription;
        $plan = $subscription?->plan;
        $plans = Plan::on('central')->where('is_active', true)->orderBy('sort_order')->get();
        $invoices = $user->tenant_id
            ? Invoice::on('central')->where('tenant_id', $user->tenant_id)->orderByDesc('period_start')->limit(12)->get()
            : collect();

        $planFeatures = $plan?->features ?? [];
        $userCount = $user->tenant_id ? User::where('tenant_id', $user->tenant_id)->count() : 0;

        return Inertia::render('Tenant/Billing/Subscription', [
            'subscription' => $subscription,
            'plan' => $plan,
            'plans' => $plans,
            'invoices' => $invoices,
            'usage' => [
                'users' => ['used' => $userCount, 'limit' => $planFeatures['max_users'] ?? 5],
                'storage' => ['used' => 0,          'limit' => ($planFeatures['storage_gb'] ?? 5) * 1024],
            ],
        ]);
    })->name('billing.index');

    Route::post('billing/cancel', fn () => back())->name('billing.cancel');
    Route::post('billing/upgrade', fn () => back())->name('billing.upgrade');

    // Usage dashboard
    Route::get('usage', fn () => Inertia::render('Tenant/Usage'))->name('usage.index');

    // Activity log
    Route::get('activity-log', fn () => Inertia::render('Tenant/ActivityLogs/Index', [
        'logs' => ['data' => [], 'links' => [], 'meta' => ['last_page' => 1]],
        'filters' => [],
        'eventTypes' => [],
        'teamMembers' => [],
    ]))->name('activity-log.index');
});

// Admin routes
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        $totalTenants = Tenant::on('central')->count();
        $activeUsers = User::count();
        $mrrCents = (int) Subscription::on('central')
            ->whereIn('status', [SubscriptionStatus::Active->value, SubscriptionStatus::Trialing->value])
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price_monthly');

        $recentTenants = Tenant::on('central')
            ->with('currentSubscription.plan')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->map(fn (Tenant $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'plan' => $t->currentSubscription?->plan?->name ?? 'None',
                'status' => $t->status->value,
                'joined' => $t->created_at?->toFormattedDateString(),
            ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_tenants' => $totalTenants,
                'active_users' => $activeUsers,
                'mrr_cents' => $mrrCents,
                'active_plans' => Plan::on('central')->where('is_active', true)->count(),
                'tenants_change' => '+'.Tenant::on('central')->whereMonth('created_at', now()->month)->count().' this month',
                'users_change' => '+'.User::whereMonth('created_at', now()->month)->count().' this month',
                'mrr_change' => '+0%',
                'plans_change' => '0',
            ],
            'recentTenants' => $recentTenants,
            'recentActivity' => [],
        ]);
    })->name('dashboard');

    // Tenants
    Route::get('tenants', function () {
        $tenants = Tenant::on('central')
            ->with('currentSubscription.plan')
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('Admin/Tenants/Index', [
            'tenants' => $tenants->through(fn (Tenant $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'domain' => $t->slug . '.app',
                'plan' => $t->currentSubscription?->plan?->name ?? 'None',
                'status' => $t->status->value,
                'users_count' => User::where('tenant_id', $t->id)->count(),
                'storage_used_mb' => 0,
                'created_at' => $t->created_at?->toFormattedDateString(),
            ]),
            'filters' => [],
        ]);
    })->name('tenants.index');

    Route::get('tenants/create', fn () => Inertia::render('Admin/Tenants/Index'))->name('tenants.create');

    Route::get('tenants/{id}', function (string $id) {
        $tenant = Tenant::on('central')
            ->with(['currentSubscription.plan', 'invoices' => fn ($q) => $q->orderByDesc('period_start')->limit(6)])
            ->findOrFail($id);

        $users = User::where('tenant_id', $id)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        return Inertia::render('Admin/Tenants/Show', [
            'tenant' => $tenant,
            'subscription' => $tenant->currentSubscription,
            'usageStats' => [],
            'activityLog' => [],
            'users' => $users,
            'invoices' => $tenant->invoices,
        ]);
    })->name('tenants.show');

    Route::patch('tenants/{tenant}/suspend', fn ($tenant) => back())->name('tenants.suspend');
    Route::delete('tenants/{tenant}', fn ($tenant) => redirect()->route('admin.tenants.index'))->name('tenants.destroy');

    // Plans
    Route::get('plans', function () {
        return Inertia::render('Admin/Plans/Index', [
            'plans' => Plan::on('central')->orderBy('sort_order')->get(),
        ]);
    })->name('plans.index');

    Route::post('plans', fn () => back())->name('plans.store');
    Route::put('plans/{plan}', fn ($p) => back())->name('plans.update');
    Route::delete('plans/{plan}', fn ($p) => back())->name('plans.destroy');

    // Subscriptions
    Route::get('subscriptions', function () {
        $subscriptions = Subscription::on('central')
            ->with(['tenant', 'plan'])
            ->orderByDesc('created_at')
            ->paginate(15);

        $summary = [
            'active' => Subscription::on('central')->where('status', SubscriptionStatus::Active)->count(),
            'trialing' => Subscription::on('central')->where('status', SubscriptionStatus::Trialing)->count(),
            'past_due' => Subscription::on('central')->where('status', SubscriptionStatus::PastDue)->count(),
            'canceled_30d' => Subscription::on('central')
                ->where('status', SubscriptionStatus::Cancelled)
                ->where('cancelled_at', '>=', now()->subDays(30))
                ->count(),
        ];

        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'filters' => [],
            'summary' => $summary,
        ]);
    })->name('subscriptions.index');

    Route::patch('subscriptions/{subscription}/cancel', fn ($s) => back())->name('subscriptions.cancel');

    // Analytics
    Route::get('analytics', function () {
        $totalTenants = Tenant::on('central')->count();
        $totalUsers = User::count();
        $mrrCents = (int) Subscription::on('central')
            ->whereIn('status', [SubscriptionStatus::Active->value, SubscriptionStatus::Trialing->value])
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price_monthly');

        $months = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
        $revenueLabels = $months->map(fn ($m) => $m->format('M Y'))->all();

        $revenueData = $months->map(fn ($m) => (int) Invoice::on('central')
            ->whereYear('period_start', $m->year)
            ->whereMonth('period_start', $m->month)
            ->sum('amount_paid')
        )->all();

        $tenantData = $months->map(fn ($m) => Tenant::on('central')
            ->whereYear('created_at', $m->year)
            ->whereMonth('created_at', $m->month)
            ->count()
        )->all();

        $planDist = Plan::on('central')
            ->withCount(['subscriptions' => fn ($q) => $q->whereIn('status', [SubscriptionStatus::Active->value, SubscriptionStatus::Trialing->value])])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/Analytics/Dashboard', [
            'kpis' => [
                'total_tenants' => $totalTenants,
                'total_users' => $totalUsers,
                'mrr_cents' => $mrrCents,
                'churn_rate' => 0.0,
                'tenants_growth' => '+'.Tenant::on('central')->whereMonth('created_at', now()->month)->count().' this month',
                'users_growth' => '+'.User::whereMonth('created_at', now()->month)->count().' this month',
                'mrr_growth' => '+0%',
                'churn_change' => '0%',
            ],
            'revenueByMonth' => ['labels' => $revenueLabels, 'data' => $revenueData],
            'tenantsByMonth' => ['labels' => $revenueLabels, 'data' => $tenantData],
            'planDistribution' => [
                'labels' => $planDist->pluck('name')->all(),
                'data' => $planDist->pluck('subscriptions_count')->all(),
            ],
            'churnByMonth' => ['labels' => $revenueLabels, 'data' => array_fill(0, 6, 0.0)],
            'topTenants' => Tenant::on('central')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn (Tenant $t, int $i) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'storage_mb' => 0,
                    'storage_pct' => 0,
                ])->all(),
        ]);
    })->name('analytics.dashboard');

    Route::get('analytics/subscriptions', fn () => Inertia::render('Admin/Analytics/SubscriptionMetrics'))
        ->name('analytics.subscriptions');

    Route::get('analytics/user-growth', fn () => Inertia::render('Admin/Analytics/UserGrowth'))
        ->name('analytics.user-growth');

    // Settings
    Route::get('settings', function () {
        return Inertia::render('Admin/Settings/SystemSettings', [
            'settings' => [],
            'dbStats' => [
                'tenants' => Tenant::on('central')->count(),
                'users' => User::count(),
                'plans' => Plan::on('central')->count(),
            ],
            'health' => ['database' => true, 'cache' => true, 'queue' => true, 'mail' => true],
        ]);
    })->name('settings.index');

    Route::post('settings', fn () => back())->name('settings.update');
});
