<?php

namespace App\Http\Controllers\Api\Admin;

use App\Admin\Services\AdminAnalyticsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexSystemAnalyticsRequest;
use Illuminate\Http\JsonResponse;

class SystemAnalyticsController extends Controller
{
    public function __construct(
        private readonly AdminAnalyticsService $analyticsService,
    ) {}

    public function index(IndexSystemAnalyticsRequest $request): JsonResponse
    {
        $data = $this->analyticsService->overview((int) $request->integer('days', 30));

        return response()->json(['data' => $data]);
    }
}
