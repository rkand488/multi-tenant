<?php

use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\SystemAnalyticsController as AdminSystemAnalyticsController;
use App\Http\Controllers\Admin\TenantController as AdminTenantController;
use App\Http\Controllers\Admin\UsageStatisticController as AdminUsageStatisticController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\InvitationController;
use App\Http\Controllers\Billing\InvoiceController;
use App\Http\Controllers\Billing\PlanController;
use App\Http\Controllers\Billing\SubscriptionController;
use App\Http\Controllers\Billing\UsageController;
use App\Http\Controllers\Tenant\ActivityLogController as TenantActivityLogController;
use App\Http\Controllers\Tenant\FileStorageController as TenantFileStorageController;
use App\Http\Controllers\Tenant\RolePermissionController as TenantRolePermissionController;
use App\Http\Controllers\Tenant\TeamSettingController as TenantTeamSettingController;
use App\Http\Controllers\Tenant\UserController as TenantUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    });

    Route::get('invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
    Route::post('invitations/accept', [InvitationController::class, 'accept'])->name('invitations.accept');

    Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
    Route::get('plans/{plan}', [PlanController::class, 'show'])->name('plans.show');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::prefix('auth')->group(function (): void {
            Route::get('me', [AuthController::class, 'me'])->name('auth.me');
            Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');
        });

        Route::prefix('admin')
            ->middleware('super_admin')
            ->name('admin.')
            ->group(function (): void {
                Route::apiResource('tenants', AdminTenantController::class)->only(['index', 'show', 'update']);
                Route::apiResource('plans', AdminPlanController::class);
                Route::apiResource('invoices', AdminInvoiceController::class)->only(['index', 'show']);
                Route::apiResource('system-analytics', AdminSystemAnalyticsController::class)->only(['index']);
                Route::apiResource('usage-statistics', AdminUsageStatisticController::class)->only(['index']);
            });

        Route::middleware(['tenant', 'tenant.active'])->group(function (): void {
            Route::post('invitations', [InvitationController::class, 'store'])->name('invitations.store');
            Route::delete('invitations/{invitation}', [InvitationController::class, 'destroy'])->name('invitations.destroy');

            Route::prefix('tenant')->name('tenant.')->group(function (): void {
                Route::apiResource('users', TenantUserController::class);
                Route::apiResource('roles-permissions', TenantRolePermissionController::class)
                    ->only(['index', 'update'])
                    ->parameters(['roles-permissions' => 'user']);
                Route::apiResource('team-settings', TenantTeamSettingController::class)
                    ->only(['index', 'update'])
                    ->parameters(['team-settings' => 'team_setting']);
                Route::apiResource('files', TenantFileStorageController::class)->only(['index', 'store', 'show', 'destroy']);
                Route::get('files/{file}/download', [TenantFileStorageController::class, 'download'])->name('files.download');
                Route::apiResource('activity-logs', TenantActivityLogController::class)->only(['index', 'show']);
            });

            Route::prefix('billing')->name('billing.')->group(function (): void {
                Route::get('subscription', [SubscriptionController::class, 'show'])->name('subscription.show');
                Route::post('subscription', [SubscriptionController::class, 'store'])->name('subscription.store');
                Route::patch('subscription', [SubscriptionController::class, 'update'])->name('subscription.update');
                Route::delete('subscription', [SubscriptionController::class, 'destroy'])->name('subscription.destroy');
                Route::post('subscription/resume', [SubscriptionController::class, 'resume'])->name('subscription.resume');

                Route::middleware('subscription')->group(function (): void {
                    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
                    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
                    Route::get('usage', [UsageController::class, 'index'])->name('usage.index');
                    Route::get('usage/{feature}', [UsageController::class, 'show'])->name('usage.show');
                });
            });
        });
    });
});
