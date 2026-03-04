<?php

namespace App\Http\Controllers\Web\Admin;

use App\Admin\Services\AdminSettingsService;
use App\Central\Models\Plan;
use App\Central\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSystemSettingsRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(private readonly AdminSettingsService $settingsService) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Settings/SystemSettings', [
            'settings' => $this->settingsService->getAll(),
            'dbStats' => [
                'tenants' => Tenant::on('central')->count(),
                'users' => User::count(),
                'plans' => Plan::on('central')->count(),
            ],
            'health' => $this->checkHealth(),
        ]);
    }

    public function update(UpdateSystemSettingsRequest $request): RedirectResponse
    {
        $this->settingsService->update($request->validated());

        return back()->with('success', 'Settings saved successfully.');
    }

    /** @return array<string, bool> */
    private function checkHealth(): array
    {
        $dbOk = true;

        try {
            \Illuminate\Support\Facades\DB::connection('central')->getPdo();
        } catch (\Throwable) {
            $dbOk = false;
        }

        $cacheOk = true;

        try {
            cache()->set('_health_check', 1, 5);
            $cacheOk = cache()->get('_health_check') === 1;
        } catch (\Throwable) {
            $cacheOk = false;
        }

        return [
            'database' => $dbOk,
            'cache' => $cacheOk,
            'queue' => true,
            'mail' => true,
        ];
    }
}
