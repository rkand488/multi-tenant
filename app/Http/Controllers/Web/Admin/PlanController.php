<?php

namespace App\Http\Controllers\Web\Admin;

use App\Central\Models\Plan;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Plans/Index', [
            'plans' => Plan::on('central')->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Plans/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug',
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
            'features' => 'nullable|array',
            'features.max_users' => 'nullable|integer',
            'features.max_storage_mb' => 'nullable|integer',
            'features.api_access' => 'boolean',
            'features.sso' => 'boolean',
            'features.custom_domain' => 'boolean',
        ]);

        // Convert dollars to cents
        $validated['price_monthly'] = (int) ($validated['price_monthly'] * 100);
        $validated['price_yearly'] = (int) ($validated['price_yearly'] * 100);

        Plan::on('central')->create($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully.');
    }

    public function show(Plan $plan): Response
    {
        return Inertia::render('Admin/Plans/Show', [
            'plan' => $plan,
        ]);
    }

    public function edit(Plan $plan): Response
    {
        return Inertia::render('Admin/Plans/Edit', [
            'plan' => $plan,
        ]);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug,'.$plan->id,
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
            'features' => 'nullable|array',
            'features.max_users' => 'nullable|integer',
            'features.max_storage_mb' => 'nullable|integer',
            'features.api_access' => 'boolean',
            'features.sso' => 'boolean',
            'features.custom_domain' => 'boolean',
        ]);

        // Convert dollars to cents
        $validated['price_monthly'] = (int) ($validated['price_monthly'] * 100);
        $validated['price_yearly'] = (int) ($validated['price_yearly'] * 100);

        $plan->update($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $plan->delete();

        return redirect()->route('admin.plans.index')->with('success', 'Plan deleted successfully.');
    }
}
