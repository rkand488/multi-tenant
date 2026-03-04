<?php

use App\Http\Controllers\Auth\WebAuthController;
use App\Http\Controllers\Web\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Web\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Web\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Web\Admin\TenantController as AdminTenantController;
use App\Http\Controllers\Web\DemoController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\Tenant\ActivityLogController;
use App\Http\Controllers\Web\Tenant\BillingController;
use App\Http\Controllers\Web\Tenant\DashboardController;
use App\Http\Controllers\Web\Tenant\RoleController;
use App\Http\Controllers\Web\Tenant\SettingsController;
use App\Http\Controllers\Web\Tenant\UsageController;
use App\Http\Controllers\Web\Tenant\UserController;
use Illuminate\Support\Facades\Route;

// Marketing landing page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Demo / screenshot pages
Route::prefix('demo')->name('demo.')->group(function () {
    Route::get('/admin', [DemoController::class, 'admin'])->name('admin');
    Route::get('/tenant', [DemoController::class, 'tenant'])->name('tenant');
    Route::get('/analytics', [DemoController::class, 'analytics'])->name('analytics');
    Route::get('/billing', [DemoController::class, 'billing'])->name('billing');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'tenant_or_super_admin'])->name('tenant.dashboard');

Route::middleware(['guest', 'tenant.optional'])->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);

    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);

    Route::get('/forgot-password', [HomeController::class, 'forgotPassword'])->name('password.request');
});

Route::post('/logout', [WebAuthController::class, 'logout'])->middleware('auth')->name('logout');

// Tenant routes
Route::middleware(['auth', 'tenant_or_super_admin'])->prefix('dashboard')->name('tenant.')->group(function () {
    // Users / team members
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('users/{user}/resend-invite', [UserController::class, 'resendInvite'])->name('users.resend-invite');

    // Roles
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
    Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
    Route::get('settings/team', [SettingsController::class, 'team'])->name('settings.team');
    Route::put('settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.update-profile');
    Route::put('settings/password', [SettingsController::class, 'updatePassword'])->name('settings.update-password');
    Route::put('settings/team', [SettingsController::class, 'updateTeam'])->name('settings.update-team');
    Route::delete('settings/team', [SettingsController::class, 'destroyTeam'])->name('settings.destroy');

    // Billing
    Route::get('billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::post('billing/upgrade', [BillingController::class, 'upgrade'])->name('billing.upgrade');

    // Usage dashboard
    Route::get('usage', [UsageController::class, 'index'])->name('usage.index');

    // Activity log
    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
});

// Admin routes
Route::middleware(['auth', 'super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Tenants
    Route::get('tenants', [AdminTenantController::class, 'index'])->name('tenants.index');
    Route::get('tenants/create', [AdminTenantController::class, 'create'])->name('tenants.create');
    Route::post('tenants', [AdminTenantController::class, 'store'])->name('tenants.store');
    Route::get('tenants/{id}', [AdminTenantController::class, 'show'])->name('tenants.show');
    Route::get('tenants/{id}/edit', [AdminTenantController::class, 'edit'])->name('tenants.edit');
    Route::put('tenants/{id}', [AdminTenantController::class, 'update'])->name('tenants.update');
    Route::patch('tenants/{tenant}/suspend', [AdminTenantController::class, 'suspend'])->name('tenants.suspend');
    Route::delete('tenants/{tenant}', [AdminTenantController::class, 'destroy'])->name('tenants.destroy');

    // Plans
    Route::get('plans', [AdminPlanController::class, 'index'])->name('plans.index');
    Route::get('plans/create', [AdminPlanController::class, 'create'])->name('plans.create');
    Route::post('plans', [AdminPlanController::class, 'store'])->name('plans.store');
    Route::get('plans/{plan}', [AdminPlanController::class, 'show'])->name('plans.show');
    Route::get('plans/{plan}/edit', [AdminPlanController::class, 'edit'])->name('plans.edit');
    Route::put('plans/{plan}', [AdminPlanController::class, 'update'])->name('plans.update');
    Route::delete('plans/{plan}', [AdminPlanController::class, 'destroy'])->name('plans.destroy');

    // Subscriptions
    Route::get('subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::patch('subscriptions/{subscription}/cancel', [AdminSubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

    // Analytics
    Route::get('analytics', [AdminAnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
    Route::get('analytics/subscriptions', [AdminAnalyticsController::class, 'subscriptions'])->name('analytics.subscriptions');
    Route::get('analytics/user-growth', [AdminAnalyticsController::class, 'userGrowth'])->name('analytics.user-growth');

    // Settings
    Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
});
