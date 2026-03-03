<?php

namespace App\Http\Controllers\Api\Admin;

use App\Admin\Services\AdminPlanService;
use App\Central\Models\Plan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use Illuminate\Http\JsonResponse;

/**
 * @tags Admin System
 */
class PlanController extends Controller
{
    public function __construct(
        private readonly AdminPlanService $planService,
    ) {}

    /**
     * List all plans (admin).
     *
     * Returns all subscription plans including inactive ones. Super admin only.
     *
     * @response object
     */
    public function index(): JsonResponse
    {
        $plans = $this->planService->listPlans();

        return response()->json($plans);
    }

    /**
     * Create a plan.
     *
     * Creates a new subscription plan. Super admin only.
     *
     * @response array{message: string, data: object}
     */
    public function store(StorePlanRequest $request): JsonResponse
    {
        $plan = $this->planService->create($request->validated());

        return response()->json([
            'message' => 'Plan created successfully.',
            'data' => $plan,
        ], 201);
    }

    /**
     * Get a plan (admin).
     *
     * Returns detailed information about a specific plan. Super admin only.
     *
     * @response array{data: object}
     */
    public function show(Plan $plan): JsonResponse
    {
        return response()->json(['data' => $plan]);
    }

    /**
     * Update a plan.
     *
     * Updates an existing subscription plan. Super admin only.
     *
     * @response array{message: string, data: object}
     */
    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $updatedPlan = $this->planService->update($plan, $request->validated());

        return response()->json([
            'message' => 'Plan updated successfully.',
            'data' => $updatedPlan,
        ]);
    }

    /**
     * Delete a plan.
     *
     * Removes a subscription plan from the system. Super admin only.
     *
     * @response array{message: string}
     */
    public function destroy(Plan $plan): JsonResponse
    {
        $this->planService->delete($plan);

        return response()->json([
            'message' => 'Plan deleted successfully.',
        ]);
    }
}
