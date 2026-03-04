<?php

namespace App\Http\Controllers\Web\Admin;

use App\Central\Enums\TenantStatus;
use App\Central\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Tenant::on('central')
            ->with('currentSubscription.plan')
            ->when($request->filled('search'), function ($q) use ($request): void {
                $term = $request->string('search')->toString();
                $q->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%")
                        ->orWhere('owner_email', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('status'), function ($q) use ($request): void {
                $status = TenantStatus::tryFrom($request->string('status')->toString());
                if ($status) {
                    $q->where('status', $status);
                }
            });

        $sortableColumns = ['name', 'slug', 'status', 'created_at'];
        $sortColumn = in_array($request->input('sort'), $sortableColumns, true) ? $request->input('sort') : 'created_at';
        $sortDirection = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortColumn, $sortDirection);

        $tenants = $query->paginate(15)->withQueryString();

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
            'filters' => (object) $request->only('search', 'status', 'sort', 'direction'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Tenants/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:central.tenants,slug'],
            'owner_email' => ['required', 'email', 'max:255'],
        ]);

        Tenant::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'owner_email' => $validated['owner_email'],
            'status' => TenantStatus::Active,
        ]);

        return redirect()->route('admin.tenants.index')
            ->with('flash', ['success' => 'Tenant created successfully.']);
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
        $tenantModel = Tenant::on('central')->findOrFail($tenant);

        $newStatus = $tenantModel->status === TenantStatus::Suspended
            ? TenantStatus::Active
            : TenantStatus::Suspended;

        $tenantModel->update(['status' => $newStatus]);

        $action = $newStatus === TenantStatus::Suspended ? 'suspended' : 'activated';

        return back()->with('flash', ['success' => "Tenant {$action} successfully."]);
    }

    public function destroy(string $tenant): RedirectResponse
    {
        $tenantModel = Tenant::on('central')->findOrFail($tenant);
        $tenantModel->delete();

        return redirect()->route('admin.tenants.index')
            ->with('flash', ['success' => 'Tenant deleted successfully.']);
    }
}
