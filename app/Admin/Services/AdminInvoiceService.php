<?php

namespace App\Admin\Services;

use App\Central\Enums\InvoiceStatus;
use App\Central\Models\Invoice;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminInvoiceService
{
    public function listInvoices(
        int $perPage = 15,
        ?string $tenantId = null,
        ?InvoiceStatus $status = null,
        ?CarbonImmutable $from = null,
        ?CarbonImmutable $to = null,
    ): LengthAwarePaginator {
        return Invoice::query()
            ->with(['tenant', 'subscription.plan'])
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate($perPage);
    }

    public function getInvoice(Invoice $invoice): Invoice
    {
        return $invoice->load(['tenant', 'subscription.plan']);
    }
}
