<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class HorizonServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->gate();
    }

    /**
     * Restrict Horizon dashboard access to super-admins only.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function (\App\Models\User $user): bool {
            return $user->isSuperAdmin();
        });
    }
}
