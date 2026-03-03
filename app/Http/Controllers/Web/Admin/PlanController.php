<?php

namespace App\Http\Controllers\Web\Admin;

use App\Admin\Services\AdminPlanService;
use App\Central\Models\Plan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\StorePlanRequest;
use App\Http\Requests\Web\Admin\UpdatePlanRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function __construct(
        private readonly AdminPlanService $planService,
    ) {}

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

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Convert dollars to cents (frontend sends dollars, DB stores cents)
        $validated['price_monthly'] = (int) ($validated['price_monthly'] * 100);
        $validated['price_yearly'] = (int) ($validated['price_yearly'] * 100);

        $this->planService->create($validated);

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

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validated();

        // Convert dollars to cents (frontend sends dollars, DB stores cents)
        if (isset($validated['price_monthly'])) {
            $validated['price_monthly'] = (int) ($validated['price_monthly'] * 100);
        }
        if (isset($validated['price_yearly'])) {
            $validated['price_yearly'] = (int) ($validated['price_yearly'] * 100);
        }

        $this->planService->update($plan, $validated);

        return redirect()->route('admin.plans.index')->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $this->planService->delete($plan);

        return redirect()->route('admin.plans.index')->with('success', 'Plan deleted successfully.');
    }
}
