<?php

namespace App\Tenant\Services;

use App\Central\Models\Tenant;
use App\Central\Models\TenantFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantFileStorageService
{
    public function listFiles(Tenant $tenant, int $perPage = 15): LengthAwarePaginator
    {
        return TenantFile::query()
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function storeFile(
        Tenant $tenant,
        UploadedFile $file,
        ?int $uploadedBy,
        string $visibility = 'private',
        array $meta = [],
    ): TenantFile {
        $filename = Str::uuid().'_'.$file->getClientOriginalName();
        $path = $file->storeAs("tenants/{$tenant->id}/files", $filename, 'local');

        return TenantFile::query()->create([
            'tenant_id' => $tenant->id,
            'uploaded_by' => $uploadedBy,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => (int) ($file->getSize() ?? 0),
            'visibility' => $visibility,
            'meta' => $meta ?: null,
        ]);
    }

    public function findFile(Tenant $tenant, string $fileId): TenantFile
    {
        return TenantFile::query()
            ->where('tenant_id', $tenant->id)
            ->whereKey($fileId)
            ->firstOrFail();
    }

    public function deleteFile(TenantFile $file): void
    {
        Storage::disk($file->disk)->delete($file->path);
        $file->delete();
    }
}
