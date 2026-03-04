<?php

namespace App\Providers;

use App\Central\Models\PersonalAccessToken;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use App\Policies\InvitationPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Invitation::class, InvitationPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        // Route model binding for central database models
        Route::bind('plan', function (string $value) {
            return \App\Central\Models\Plan::on('central')->findOrFail($value);
        });

        // All factories live in Database\Factories regardless of model namespace.
        // Strip sub-namespaces so e.g. App\Central\Models\Plan resolves to
        // Database\Factories\PlanFactory instead of
        // Database\Factories\Central\Models\PlanFactory.
        Factory::guessFactoryNamesUsing(function (string $modelName): string {
            $shortName = class_basename($modelName);

            return 'Database\\Factories\\'.$shortName.'Factory';
        });
    }
}
