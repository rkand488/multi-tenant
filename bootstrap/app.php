<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'tenant' => \App\Http\Middleware\IdentifyTenant::class,
            'tenant.optional' => \App\Http\Middleware\IdentifyTenantIfPresent::class,
            'tenant.active' => \App\Http\Middleware\EnsureTenantIsActive::class,
            'subscription' => \App\Http\Middleware\RequireActiveSubscription::class,
            'feature' => \App\Http\Middleware\CheckFeatureAccess::class,
            'usage.limit' => \App\Http\Middleware\EnforceUsageLimit::class,
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'tenant_or_super_admin' => \App\Http\Middleware\EnsureTenantOrSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
