<?php

namespace App\Http\Resources;

use App\Central\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Subscription */
class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'billing_interval' => $this->billing_interval->value,
            'trial_ends_at' => $this->trial_ends_at?->toIso8601String(),
            'current_period_start' => $this->current_period_start?->toIso8601String(),
            'current_period_end' => $this->current_period_end?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
