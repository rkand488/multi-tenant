<?php

namespace App\Http\Resources;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Role */
class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'permissions' => $this->permissions ?? [],
            'is_system' => (bool) ($this->is_system ?? false),
            'tenant_id' => $this->tenant_id,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
