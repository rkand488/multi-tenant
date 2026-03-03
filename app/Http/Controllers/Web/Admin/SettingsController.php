<?php

namespace App\Http\Controllers\Web\Admin;

use App\Central\Models\Plan;
use App\Central\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Settings/SystemSettings', [
            'settings' => [],
            'dbStats' => [
                'tenants' => Tenant::on('central')->count(),
                'users' => User::count(),
                'plans' => Plan::on('central')->count(),
            ],
            'health' => ['database' => true, 'cache' => true, 'queue' => true, 'mail' => true],
        ]);
    }

    public function update(): RedirectResponse
    {
        return back();
    }
}
