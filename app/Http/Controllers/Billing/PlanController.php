<?php

namespace App\Http\Controllers\Billing;

use App\Billing\Services\PlanService;
use App\Central\Models\Plan;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    public function __construct(
        private readonly PlanService $planService,
    ) {}

    /**
     * List all publicly active plans.
     */
    public function index(): JsonResponse
    {
        $plans = $this->planService->listActive();

        return response()->json(['data' => $plans]);
    }

    /**
     * Show a single plan.
     */
    public function show(Plan $plan): JsonResponse
    {
        return response()->json(['data' => $plan]);
    }
}
