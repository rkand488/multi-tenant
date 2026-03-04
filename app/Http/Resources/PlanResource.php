<?php

namespace App\Http\Resources;

use App\Central\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Plan */
class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price_monthly' => $this->price_monthly,
            'price_monthly_formatted' => '$'.number_format($this->price_monthly / 100, 2),
            'price_yearly' => $this->price_yearly,
            'price_yearly_formatted' => '$'.number_format($this->price_yearly / 100, 2),
            'trial_days' => $this->trial_days,
            'features' => $this->features,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }
}
