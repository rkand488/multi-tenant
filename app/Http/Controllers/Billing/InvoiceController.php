<?php

namespace App\Http\Controllers\Billing;

use App\Billing\Services\InvoiceService;
use App\Central\Models\Invoice;
use App\Http\Controllers\Controller;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * Paginated list of invoices for the current tenant.
     */
    public function index(): JsonResponse
    {
        $tenant = $this->tenantContext->get();
        $invoices = $this->invoiceService->listForTenant($tenant);

        return response()->json($invoices);
    }

    /**
     * Show a single invoice belonging to the current tenant.
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $tenant = $this->tenantContext->get();

        if ((string) $invoice->tenant_id !== (string) $tenant->id) {
            return response()->json(['message' => 'Not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $invoice]);
    }
}
