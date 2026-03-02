<?php

use App\Http\Controllers\Auth\WebAuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Legacy welcome page — remove once full Inertia pages are in place
Route::get('/', function () {
    return view('welcome');
});

// ── Example Inertia routes ─────────────────────────────────────────────────

Route::get('/dashboard', function () {
    return Inertia::render('Tenant/Dashboard', [
        'stats' => [
            'user_count' => 1,
            'user_limit' => 5,
            'storage_used_mb' => 0,
            'storage_limit_mb' => 500,
            'active_sessions' => 0,
            'subscription_status' => 'active',
        ],
        'plan' => ['name' => 'Starter', 'interval' => 'mo'],
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

// Tenant routes (stubs — replace with real controllers in later phases)
Route::middleware('auth')->prefix('dashboard')->name('tenant.')->group(function () {
    // Users / team members
    Route::get('users', fn () => Inertia::render('Tenant/Users/Index', [
        'users' => ['data' => [], 'links' => [], 'meta' => ['last_page' => 1]],
        'roles' => [],
        'filters' => [],
        'canInvite' => true,
    ]))->name('users.index');

    Route::get('users/create', fn () => Inertia::render('Tenant/Users/Create', [
        'roles' => [],
        'defaultRoleId' => null,
        'canInvite' => true,
        'slotsRemaining' => null,
    ]))->name('users.create');

    Route::post('users', fn () => back())->name('users.store');
    Route::patch('users/{user}', fn ($user) => back())->name('users.update');
    Route::delete('users/{user}', fn ($user) => back())->name('users.destroy');
    Route::post('users/{user}/resend-invite', fn ($user) => back())->name('users.resend-invite');

    // Roles
    Route::get('roles', fn () => Inertia::render('Tenant/Roles/Index', [
        'roles' => [],
        'availablePermissions' => [],
    ]))->name('roles.index');

    Route::post('roles', fn () => back())->name('roles.store');
    Route::put('roles/{role}', fn ($role) => back())->name('roles.update');
    Route::delete('roles/{role}', fn ($role) => back())->name('roles.destroy');

    // Settings
    Route::get('settings', fn () => redirect()->route('tenant.settings.profile'))->name('settings.index');

    Route::get('settings/profile', fn () => Inertia::render('Tenant/Settings/Profile', [
        'user' => ['id' => 1, 'name' => 'Demo User', 'email' => 'demo@example.com'],
    ]))->name('settings.profile');

    Route::get('settings/team', fn () => Inertia::render('Tenant/Settings/Team', [
        'tenant' => ['id' => 1, 'name' => 'Demo Workspace', 'slug' => 'demo', 'created_at' => now()->toDateString()],
        'timezones' => \DateTimeZone::listIdentifiers(),
    ]))->name('settings.team');

    Route::put('settings/profile', fn () => back())->name('settings.update-profile');
    Route::put('settings/password', fn () => back())->name('settings.update-password');
    Route::put('settings/team', fn () => back())->name('settings.update-team');
    Route::delete('settings/team', fn () => redirect('/'))->name('settings.destroy');

    // Billing
    Route::get('billing', fn () => Inertia::render('Tenant/Billing/Subscription', [
        'subscription' => null,
        'plan' => null,
        'plans' => [],
        'invoices' => [],
        'usage' => ['users' => ['used' => 1, 'limit' => 5], 'storage' => ['used' => 0, 'limit' => 500]],
    ]))->name('billing.index');

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

// Admin stub routes (replace with full controllers in later phases)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => Inertia::render('Admin/Dashboard', [
        'stats' => [
            'total_tenants' => 0,
            'active_users' => 0,
            'mrr_cents' => 0,
            'active_plans' => 0,
            'tenants_change' => '+0%',
            'users_change' => '+0%',
            'mrr_change' => '+0%',
            'plans_change' => '0',
        ],
        'recentTenants' => [],
        'recentActivity' => [],
    ]))->name('dashboard');

    // Tenants
    Route::get('tenants', fn () => Inertia::render('Admin/Tenants/Index', [
        'tenants' => ['data' => [], 'links' => [], 'meta' => ['last_page' => 1]],
        'filters' => [],
    ]))->name('tenants.index');

    Route::get('tenants/create', fn () => Inertia::render('Admin/Tenants/Index'))->name('tenants.create');

    Route::get('tenants/{tenant}', fn ($tenant) => Inertia::render('Admin/Tenants/Show', [
        'tenant' => ['id' => $tenant, 'name' => 'Demo Tenant', 'status' => 'active'],
        'subscription' => null,
        'usageStats' => [],
        'activityLog' => [],
        'users' => [],
        'invoices' => [],
    ]))->name('tenants.show');

    Route::patch('tenants/{tenant}/suspend', fn ($tenant) => back())->name('tenants.suspend');
    Route::delete('tenants/{tenant}', fn ($tenant) => redirect()->route('admin.tenants.index'))->name('tenants.destroy');

    // Plans
    Route::get('plans', fn () => Inertia::render('Admin/Plans/Index', ['plans' => []]))->name('plans.index');
    Route::post('plans', fn () => back())->name('plans.store');
    Route::put('plans/{plan}', fn ($p) => back())->name('plans.update');
    Route::delete('plans/{plan}', fn ($p) => back())->name('plans.destroy');

    // Subscriptions
    Route::get('subscriptions', fn () => Inertia::render('Admin/Subscriptions/Index', [
        'subscriptions' => ['data' => [], 'links' => [], 'meta' => ['last_page' => 1]],
        'filters' => [],
        'summary' => ['active' => 0, 'trialing' => 0, 'past_due' => 0, 'canceled_30d' => 0],
    ]))->name('subscriptions.index');
    Route::patch('subscriptions/{subscription}/cancel', fn ($s) => back())->name('subscriptions.cancel');

    // Analytics
    Route::get('analytics', fn () => Inertia::render('Admin/Analytics/Dashboard', [
        'kpis' => [
            'total_tenants' => 1284,
            'total_users' => 9471,
            'mrr_cents' => 4829000,
            'churn_rate' => 2.1,
            'tenants_growth' => '+8.2%',
            'users_growth' => '+15.7%',
            'mrr_growth' => '+12.4%',
            'churn_change' => '-0.3%',
        ],
        'revenueByMonth' => ['labels' => ['Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'], 'data' => [35800, 38400, 41200, 43900, 46100, 48290]],
        'tenantsByMonth' => ['labels' => ['Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'], 'data' => [68, 74, 81, 88, 97, 103]],
        'planDistribution' => ['labels' => ['Starter', 'Growth', 'Pro', 'Enterprise'], 'data' => [393, 236, 153, 47]],
        'churnByMonth' => ['labels' => ['Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'], 'data' => [2.4, 1.8, 2.2, 2.9, 1.6, 2.1]],
        'topTenants' => [
            ['id' => 1, 'name' => 'Globex Technologies',  'storage_mb' => 8420, 'storage_pct' => 84],
            ['id' => 2, 'name' => 'Initech Solutions',    'storage_mb' => 7180, 'storage_pct' => 72],
            ['id' => 3, 'name' => 'Umbrella Corp',        'storage_mb' => 6340, 'storage_pct' => 63],
            ['id' => 4, 'name' => 'Dunder Mifflin Inc.',  'storage_mb' => 5910, 'storage_pct' => 59],
            ['id' => 5, 'name' => 'Sterling Cooper',      'storage_mb' => 4780, 'storage_pct' => 48],
        ],
    ]))->name('analytics.dashboard');

    Route::get('analytics/subscriptions', fn () => Inertia::render('Admin/Analytics/SubscriptionMetrics'))
        ->name('analytics.subscriptions');

    Route::get('analytics/user-growth', fn () => Inertia::render('Admin/Analytics/UserGrowth'))
        ->name('analytics.user-growth');

    // Settings
    Route::get('settings', fn () => Inertia::render('Admin/Settings/SystemSettings', [
        'settings' => [],
        'dbStats' => ['tenants' => 0, 'tables' => 0, 'size' => '0 MB'],
        'health' => ['database' => true, 'cache' => true, 'queue' => true, 'mail' => true],
    ]))->name('settings.index');
    Route::post('settings', fn () => back())->name('settings.update');
});
