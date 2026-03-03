<?php

namespace App\Http\Controllers\Web\Admin;

use App\Central\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(): Response
    {
        $tenants = Tenant::on('central')
            ->with('currentSubscription.plan')
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('Admin/Tenants/Index', [
            'tenants' => $tenants->through(fn (Tenant $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'domain' => $t->slug.'.app',
                'plan' => $t->currentSubscription?->plan?->name ?? 'None',
                'status' => $t->status->value,
                'users_count' => User::where('tenant_id', $t->id)->count(),
                'storage_used_mb' => 0,
                'created_at' => $t->created_at?->toFormattedDateString(),
            ]),
            'filters' => [],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Tenants/Create');
    }

    public function show(string $id): Response
    {
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
    }

    public function edit(string $id): Response
    {
        $tenant = Tenant::on('central')
            ->with('currentSubscription.plan')
            ->findOrFail($id);

        return Inertia::render('Admin/Tenants/Edit', [
            'tenant' => $tenant,
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        return back();
    }

    public function suspend(string $tenant): RedirectResponse
    {
        return back();
    }

    public function destroy(string $tenant): RedirectResponse
    {
        return redirect()->route('admin.tenants.index');
    }
}
