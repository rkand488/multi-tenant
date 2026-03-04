<?php

namespace App\Http\Resources;

use App\Central\Models\TenantFile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TenantFile */
class FileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'disk' => $this->disk,
            'path' => $this->path,
            'visibility' => $this->visibility,
            'meta' => $this->meta,
            'uploader' => $this->whenLoaded('uploader', fn () => [
                'id' => $this->uploader?->id,
                'name' => $this->uploader?->name,
            ]),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
