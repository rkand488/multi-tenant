<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Services\AdminInvoiceService;
use App\Central\Enums\InvoiceStatus;
use App\Central\Models\Invoice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexInvoicesRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly AdminInvoiceService $invoiceService,
    ) {}

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

    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json([
            'data' => $this->invoiceService->getInvoice($invoice),
        ]);
    }
}
