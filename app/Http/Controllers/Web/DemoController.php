<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DemoController extends Controller
{
    public function admin(): Response
    {
        return Inertia::render('Demo/AdminDashboardDemo');
    }

    public function tenant(): Response
    {
        return Inertia::render('Demo/TenantDashboardDemo');
    }

    public function analytics(): Response
    {
        return Inertia::render('Demo/AnalyticsDemo');
    }

    public function billing(): Response
    {
        return Inertia::render('Demo/BillingDemo');
    }
}
