<?php

namespace App\Http\Resources;

use App\Central\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Tenant */
class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'owner_email' => $this->owner_email,
            'trial_ends_at' => $this->trial_ends_at?->toIso8601String(),
            'primary_domain' => $this->whenLoaded('primaryDomain', fn () => $this->primaryDomain?->domain),
            'plan' => new PlanResource($this->whenLoaded('currentSubscription', fn () => $this->currentSubscription?->plan)),
            'subscription_status' => $this->whenLoaded('currentSubscription', fn () => $this->currentSubscription?->status->value),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
