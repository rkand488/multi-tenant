<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class UsageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Tenant/Usage');
    }
}
