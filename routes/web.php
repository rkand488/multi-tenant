<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\WebAuthController;
use App\Http\Controllers\Web\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Web\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Web\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Web\Admin\TenantController as AdminTenantController;
use App\Http\Controllers\Web\DemoController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\Invitation\AcceptInvitationController;
use App\Http\Controllers\Web\Tenant\ActivityLogController;
use App\Http\Controllers\Web\Tenant\AuditLogController;
use App\Http\Controllers\Web\Tenant\BillingController;
use App\Http\Controllers\Web\Tenant\DashboardController;
use App\Http\Controllers\Web\Tenant\FileController;
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

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'tenant.optional', 'tenant_or_super_admin'])->name('tenant.dashboard');

Route::middleware(['guest', 'tenant.optional'])->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);

    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Invitation accept (web) — accessible without authentication
Route::get('/invitations/{token}/accept', [AcceptInvitationController::class, 'show'])->name('invitations.accept.show');
Route::post('/invitations/{token}/accept', [AcceptInvitationController::class, 'accept'])->name('invitations.accept');

Route::post('/logout', [WebAuthController::class, 'logout'])->middleware('auth')->name('logout');

// Email verification
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');
});

// Tenant routes
Route::middleware(['auth', 'tenant.optional', 'tenant_or_super_admin'])->prefix('dashboard')->name('tenant.')->group(function () {
    // Users / team members
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('users/{user}/resend-invite', [UserController::class, 'resendInvite'])->name('users.resend-invite');

    // Roles
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
    Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // Files
    Route::get('files', [FileController::class, 'index'])->name('files.index');
    Route::post('files', [FileController::class, 'store'])->name('files.store');
    Route::delete('files/{file}', [FileController::class, 'destroy'])->name('files.destroy');
    Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');

    // Audit logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('settings/api-tokens', [SettingsController::class, 'apiTokens'])->name('settings.api-tokens');
    Route::get('settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
    Route::get('settings/team', [SettingsController::class, 'team'])->name('settings.team');
    Route::put('settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.update-profile');
    Route::put('settings/password', [SettingsController::class, 'updatePassword'])->name('settings.update-password');
    Route::put('settings/team', [SettingsController::class, 'updateTeam'])->middleware('verified')->name('settings.update-team');
    Route::delete('settings/team', [SettingsController::class, 'destroyTeam'])->middleware('verified')->name('settings.destroy');
    Route::post('settings/team/transfer-ownership', [SettingsController::class, 'transferOwnership'])->middleware('verified')->name('settings.transfer-ownership');

    // Billing (cancel/upgrade require verified email)
    Route::get('billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('billing/plans', [BillingController::class, 'plans'])->name('billing.plans');
    Route::get('billing/invoices', [BillingController::class, 'invoices'])->name('billing.invoices');
    Route::get('billing/invoices/{invoice}', [BillingController::class, 'showInvoice'])->name('billing.invoices.show');
    Route::post('billing/cancel', [BillingController::class, 'cancel'])->middleware('verified')->name('billing.cancel');
    Route::post('billing/upgrade', [BillingController::class, 'upgrade'])->middleware('verified')->name('billing.upgrade');

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
