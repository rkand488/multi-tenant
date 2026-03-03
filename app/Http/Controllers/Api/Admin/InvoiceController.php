<?php

namespace App\Http\Controllers\Api\Admin;

use App\Admin\Services\AdminInvoiceService;
use App\Central\Enums\InvoiceStatus;
use App\Central\Models\Invoice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexInvoicesRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

/**
 * @tags Admin System
 */
class InvoiceController extends Controller
{
    public function __construct(
        private readonly AdminInvoiceService $invoiceService,
    ) {}

    /**
     * List all invoices (admin).
     *
     * Returns a paginated list of all invoices across all tenants. Super admin only.
     *
     * @response object
     */
    public function index(IndexInvoicesRequest $request): JsonResponse
    {
        $invoices = $this->invoiceService->listInvoices(
            perPage: (int) $request->integer('per_page', 15),
            tenantId: $request->string('tenant_id')->toString() ?: null,
            status: $request->filled('status') ? InvoiceStatus::from($request->string('status')->toString()) : null,
            from: $request->filled('from') ? CarbonImmutable::parse($request->string('from')->toString()) : null,
            to: $request->filled('to') ? CarbonImmutable::parse($request->string('to')->toString()) : null,
        );

        return response()->json($invoices);
    }

    /**
     * Get an invoice (admin).
     *
     * Returns detailed information about a specific invoice. Super admin only.
     *
     * @response array{data: object}
     */
    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json([
            'data' => $this->invoiceService->getInvoice($invoice),
        ]);
    }
}
