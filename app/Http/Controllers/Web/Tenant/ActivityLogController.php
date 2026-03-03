<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Tenant/ActivityLogs/Index', [
            'logs' => ['data' => [], 'links' => [], 'meta' => ['last_page' => 1]],
            'filters' => [],
            'eventTypes' => [],
            'teamMembers' => [],
        ]);
    }
}
