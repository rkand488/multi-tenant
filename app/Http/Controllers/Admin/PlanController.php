<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Services\AdminPlanService;
use App\Central\Models\Plan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    public function __construct(
        private readonly AdminPlanService $planService,
    ) {}

    public function index(): JsonResponse
    {
        $plans = $this->planService->listPlans();

        return response()->json($plans);
    }

    public function store(StorePlanRequest $request): JsonResponse
    {
        $plan = $this->planService->create($request->validated());

        return response()->json([
            'message' => 'Plan created successfully.',
            'data' => $plan,
        ], 201);
    }

    public function show(Plan $plan): JsonResponse
    {
        return response()->json(['data' => $plan]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $updatedPlan = $this->planService->update($plan, $request->validated());

        return response()->json([
            'message' => 'Plan updated successfully.',
            'data' => $updatedPlan,
        ]);
    }

    public function destroy(Plan $plan): JsonResponse
    {
        $this->planService->delete($plan);

        return response()->json([
            'message' => 'Plan deleted successfully.',
        ]);
    }
}
