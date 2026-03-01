<?php

namespace App\Tenancy\Contracts;

use App\Central\Models\Tenant;
use Illuminate\Http\Request;

interface FindsTenant
{
    /**
     * Resolve the tenant for an incoming HTTP request.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function fromRequest(Request $request): Tenant;
}
