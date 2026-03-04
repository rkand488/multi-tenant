<?php

namespace App\Http\Resources;

use App\Central\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Invoice */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'amount_due' => $this->amount_due,
            'amount_paid' => $this->amount_paid,
            'amount_outstanding' => $this->amountOutstanding(),
            'formatted_total' => $this->formattedTotal(),
            'currency' => $this->currency,
            'billing_interval' => $this->billing_interval?->value,
            'period_start' => $this->period_start?->toIso8601String(),
            'period_end' => $this->period_end?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'due_date' => $this->due_date?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
